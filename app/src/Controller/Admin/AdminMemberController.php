<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\MemberPermissionsType;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\Exception\MemberModerationException;
use App\Service\MemberModerationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/members', name: 'admin_member_')]
final class AdminMemberController extends AbstractController
{
    private const STEP = 20;
    private const MAX_LIMIT = 200;

    #[Route('', name: 'index')]
    #[IsGranted('ROLE_ADMIN_MEMBERS')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $limit = $this->resolveLimit($request->query->getInt('limit', self::STEP));
        $q = trim((string) $request->query->get('q', '')) ?: null;
        $type = $request->query->get('type');
        $type = \in_array($type, ['member', 'admin'], true) ? $type : null;

        $members = $userRepository->findFilteredMembers($q, $type, $limit);
        $total = $userRepository->countFilteredMembers($q, $type);

        return $this->render('admin/member/members_index.html.twig', [
            'members' => $members,
            'total' => $total,
            'hasMore' => $total > $limit,
            'canShowLess' => $limit > self::STEP,
            'limit' => $limit,
            'q' => $q,
            'type' => $type,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN_MEMBERS')]
    public function show(User $member, OrderRepository $orderRepository, Request $request, MemberModerationService $moderation): Response
    {
        $permissionsForm = $this->createForm(MemberPermissionsType::class, [
            'roles' => array_intersect($member->getAssignedRoles(), array_keys(User::MANAGEABLE_ROLES)),
        ], [
            'action' => $this->generateUrl('admin_member_permissions', ['id' => $member->getId()]),
            'method' => 'POST',
        ]);

        /** @var User $actor */
        $actor = $this->getUser();

        return $this->render('admin/member/member_show.html.twig', [
            'member' => $member,
            'orders' => $orderRepository->findByUserOrderedByDate($member),
            'permissionsForm' => $permissionsForm->createView(),
            'canModerate' => $moderation->canModerate($actor, $member),
            'backQuery' => [
                'q' => $request->query->get('q'),
                'limit' => $request->query->get('limit'),
            ],
        ]);
    }

    #[Route('/{id}/permissions', name: 'permissions', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function updatePermissions(User $member, Request $request, MemberModerationService $moderation): Response
    {
        $form = $this->createForm(MemberPermissionsType::class, [
            'roles' => array_intersect($member->getAssignedRoles(), array_keys(User::MANAGEABLE_ROLES)),
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Formulaire de permissions invalide.');

            return $this->redirectToRoute('admin_member_show', ['id' => $member->getId()]);
        }

        /** @var User $actor */
        $actor = $this->getUser();

        try {
            $result = $moderation->updatePermissions($actor, $member, $form->getData()['roles']);
            $this->addFlash('success', ($result['added'] === [] && $result['removed'] === []) ? 'Aucun changement de permission.' : 'Permissions mises à jour.');
        } catch (MemberModerationException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_member_show', ['id' => $member->getId()]);
    }

    #[Route('/{id}/suspend', name: 'suspend', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN_MEMBERS')]
    public function suspend(User $member, Request $request, MemberModerationService $moderation): Response
    {
        if (!$this->isCsrfTokenValid('suspend_member_' . $member->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $actor */
        $actor = $this->getUser();

        try {
            $moderation->suspend($actor, $member);
            $this->addFlash('success', 'Compte suspendu.');
        } catch (MemberModerationException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_member_show', ['id' => $member->getId()]);
    }

    #[Route('/{id}/reactivate', name: 'reactivate', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN_MEMBERS')]
    public function reactivate(User $member, Request $request, MemberModerationService $moderation): Response
    {
        if (!$this->isCsrfTokenValid('reactivate_member_' . $member->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $actor */
        $actor = $this->getUser();

        try {
            $moderation->reactivate($actor, $member);
            $this->addFlash('success', 'Compte réactivé.');
        } catch (MemberModerationException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_member_show', ['id' => $member->getId()]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN_MEMBERS')]
    public function delete(User $member, Request $request, MemberModerationService $moderation): Response
    {
        if (!$this->isCsrfTokenValid('delete_member_' . $member->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $actor */
        $actor = $this->getUser();

        try {
            $result = $moderation->removeAccount($actor, $member);
            $this->addFlash('success', $result === 'deleted' ? 'Compte supprimé.' : 'Compte anonymisé : des données liées (commandes ou serveurs créés) devaient être conservées.');
        } catch (MemberModerationException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('admin_member_show', ['id' => $member->getId()]);
        }

        return $this->redirectToRoute('admin_member_index');
    }

    private function resolveLimit(int $raw): int
    {
        $limit = max(self::STEP, min(self::MAX_LIMIT, $raw));

        return $limit - ($limit % self::STEP);
    }
}
