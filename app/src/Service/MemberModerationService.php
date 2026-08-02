<?php

namespace App\Service;

use App\Entity\AdminActivityLog;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\ServerRepository;
use App\Repository\UserRepository;
use App\Service\Exception\MemberModerationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Centralise toutes les actions de modération d'un membre depuis sa fiche administrative
 * (permissions, suspension, suppression) : garde-fous, application, et écriture de
 * l'historique administratif, pour ne pas disperser cette logique dans le contrôleur.
 */
final class MemberModerationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdminActivityLogger $activityLogger,
        private readonly UserRepository $userRepository,
        private readonly ServerRepository $serverRepository,
        private readonly OrderRepository $orderRepository,
        private readonly ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * Seul un ROLE_SUPER_ADMIN atteint cette méthode (vérifié par IsGranted sur la route) :
     * aucun contrôle "canModerate" supplémentaire n'est donc nécessaire ici.
     *
     * @param string[] $submittedRoles rôles cochés dans le formulaire, non filtrés
     *
     * @return array{added: string[], removed: string[]}
     */
    public function updatePermissions(User $actor, User $member, array $submittedRoles): array
    {
        $this->assertNotSelf($actor, $member, 'modifier les permissions de');

        $manageable = array_keys(User::MANAGEABLE_ROLES);
        $requested = array_values(array_intersect($submittedRoles, $manageable));
        $current = array_values(array_intersect($member->getAssignedRoles(), $manageable));

        $added = array_values(array_diff($requested, $current));
        $removed = array_values(array_diff($current, $requested));

        if ($added === [] && $removed === []) {
            return ['added' => [], 'removed' => []];
        }

        $actorMaxLevel = $this->maxLevel($actor->getAssignedRoles());
        foreach ($added as $role) {
            if ((User::ROLE_LEVELS[$role] ?? 0) > $actorMaxLevel) {
                throw new MemberModerationException(sprintf(
                    'Vous ne pouvez pas accorder le rôle "%s", supérieur à votre propre niveau d\'autorisation.',
                    User::MANAGEABLE_ROLES[$role] ?? $role,
                ));
            }
        }

        if (in_array('ROLE_SUPER_ADMIN', $removed, true) && $this->isLastSuperAdmin($member)) {
            throw new MemberModerationException('Impossible de retirer le rôle Super-administrateur au dernier super-administrateur.');
        }

        $this->em->wrapInTransaction(function () use ($actor, $member, $requested, $added, $removed): void {
            $member->setRoles(array_values(array_unique(array_merge(['ROLE_USER'], $requested))));

            if (in_array('ROLE_SUPER_ADMIN', $added, true)) {
                $this->activityLogger->log($actor, AdminActivityLog::ACTION_MEMBER_PROMOTED_SUPER_ADMIN, AdminActivityLog::SUBJECT_MEMBER, $member->getId(), $member->getPseudo());
            } elseif (in_array('ROLE_ADMIN', $added, true)) {
                $this->activityLogger->log($actor, AdminActivityLog::ACTION_MEMBER_PROMOTED_ADMIN, AdminActivityLog::SUBJECT_MEMBER, $member->getId(), $member->getPseudo());
            }

            if (in_array('ROLE_SUPER_ADMIN', $removed, true) || in_array('ROLE_ADMIN', $removed, true)) {
                $this->activityLogger->log($actor, AdminActivityLog::ACTION_MEMBER_DEMOTED, AdminActivityLog::SUBJECT_MEMBER, $member->getId(), $member->getPseudo());
            }

            $grantedPermissions = array_diff($added, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
            if ($grantedPermissions !== []) {
                $this->activityLogger->log($actor, AdminActivityLog::ACTION_MEMBER_PERMISSION_GRANTED, AdminActivityLog::SUBJECT_MEMBER, $member->getId(), $member->getPseudo(), $this->labelList($grantedPermissions));
            }

            $revokedPermissions = array_diff($removed, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
            if ($revokedPermissions !== []) {
                $this->activityLogger->log($actor, AdminActivityLog::ACTION_MEMBER_PERMISSION_REVOKED, AdminActivityLog::SUBJECT_MEMBER, $member->getId(), $member->getPseudo(), $this->labelList($revokedPermissions));
            }
        });

        return ['added' => $added, 'removed' => $removed];
    }

    public function suspend(User $actor, User $member): void
    {
        $this->assertNotSelf($actor, $member, 'suspendre');
        $this->assertCanModerate($actor, $member, 'suspendre');

        if ($this->isLastSuperAdmin($member)) {
            throw new MemberModerationException('Impossible de suspendre le dernier super-administrateur.');
        }

        if ($member->isSuspended()) {
            return;
        }

        $this->em->wrapInTransaction(function () use ($actor, $member): void {
            $member->setSuspendedAt(new \DateTimeImmutable());
            $this->activityLogger->log($actor, AdminActivityLog::ACTION_MEMBER_SUSPENDED, AdminActivityLog::SUBJECT_MEMBER, $member->getId(), $member->getPseudo());
        });
    }

    public function reactivate(User $actor, User $member): void
    {
        $this->assertNotSelf($actor, $member, 'réactiver');
        $this->assertCanModerate($actor, $member, 'réactiver');

        if (!$member->isSuspended()) {
            return;
        }

        $this->em->wrapInTransaction(function () use ($actor, $member): void {
            $member->setSuspendedAt(null);
            $this->activityLogger->log($actor, AdminActivityLog::ACTION_MEMBER_REACTIVATED, AdminActivityLog::SUBJECT_MEMBER, $member->getId(), $member->getPseudo());
        });
    }

    /**
     * @return 'deleted'|'anonymized'
     */
    public function removeAccount(User $actor, User $member): string
    {
        $this->assertNotSelf($actor, $member, 'supprimer');
        $this->assertCanModerate($actor, $member, 'supprimer');

        if ($this->isLastSuperAdmin($member)) {
            throw new MemberModerationException('Impossible de supprimer le dernier super-administrateur.');
        }

        // Server::$createdBy est non-nullable : suppression réelle impossible si le membre a
        // créé un serveur. La présence de commandes fait aussi préférer l'anonymisation, pour
        // garder une fiche identifiable en cas d'audit, même si Order conserve de toute façon
        // son propre customerEmail/customerPseudo indépendamment du User.
        $mustAnonymize = $this->serverRepository->countByCreator($member) > 0
            || $this->orderRepository->countByUser($member) > 0;

        if ($mustAnonymize) {
            $this->em->wrapInTransaction(function () use ($actor, $member): void {
                $this->anonymize($actor, $member);
            });

            return 'anonymized';
        }

        $this->em->wrapInTransaction(function () use ($actor, $member): void {
            $this->hardDelete($actor, $member);
        });

        return 'deleted';
    }

    /**
     * True si $actor peut modérer $member : les permissions "membres" suffisent pour un
     * membre non-administrateur, mais modérer un autre administrateur (suspendre,
     * anonymiser, supprimer) exige que $actor détienne lui-même ROLE_SUPER_ADMIN.
     */
    public function canModerate(User $actor, User $member): bool
    {
        if (!$member->isAdministrator()) {
            return true;
        }

        return in_array('ROLE_SUPER_ADMIN', $actor->getAssignedRoles(), true);
    }

    private function anonymize(User $actor, User $member): void
    {
        $id = $member->getId();
        $label = $member->getPseudo();

        $member->setPseudo('Membre supprimé #' . $id);
        $member->setEmail(sprintf('membre-supprime-%d@anonymise.local', $id));
        $member->setPassword($this->passwordHasher->hashPassword($member, bin2hex(random_bytes(32))));
        $member->setRoles(['ROLE_USER']);
        $member->setIsVerified(false);
        $member->setSuspendedAt(new \DateTimeImmutable());
        $member->setAnonymizedAt(new \DateTimeImmutable());

        $this->activityLogger->log($actor, AdminActivityLog::ACTION_MEMBER_ANONYMIZED, AdminActivityLog::SUBJECT_MEMBER, $id, $label);
    }

    private function hardDelete(User $actor, User $member): void
    {
        $id = $member->getId();
        $label = $member->getPseudo();

        // Jetons de réinitialisation éventuels : User::$user y est non-nullable, ils
        // bloqueraient sinon la suppression sans représenter une donnée à conserver.
        $this->resetPasswordRequestRepository->removeRequests($member);

        $this->em->remove($member);
        $this->em->flush();

        $this->activityLogger->log($actor, AdminActivityLog::ACTION_MEMBER_DELETED, AdminActivityLog::SUBJECT_MEMBER, $id, $label);
    }

    private function assertNotSelf(User $actor, User $member, string $action): void
    {
        if ($actor->getId() === $member->getId()) {
            throw new MemberModerationException(sprintf('Vous ne pouvez pas %s votre propre compte.', $action));
        }
    }

    private function assertCanModerate(User $actor, User $member, string $action): void
    {
        if (!$this->canModerate($actor, $member)) {
            throw new MemberModerationException(sprintf('Seul un super-administrateur peut %s le compte d\'un autre administrateur.', $action));
        }
    }

    private function isLastSuperAdmin(User $member): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $member->getAssignedRoles(), true)
            && $this->userRepository->countByRole('ROLE_SUPER_ADMIN') <= 1;
    }

    /**
     * @param string[] $roles
     */
    private function maxLevel(array $roles): int
    {
        $level = 0;
        foreach ($roles as $role) {
            $level = max($level, User::ROLE_LEVELS[$role] ?? 0);
        }

        return $level;
    }

    /**
     * @param string[] $roles
     */
    private function labelList(array $roles): string
    {
        return implode(', ', array_map(static fn (string $role): string => User::MANAGEABLE_ROLES[$role] ?? $role, $roles));
    }
}
