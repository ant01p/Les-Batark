<?php

namespace App\Controller;

use App\Entity\AdminActivityLog;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\AdminActivityLogger;
use App\Service\PasswordChangeNotifier;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/account', name: 'account_')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, OrderRepository $orderRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'orders' => $orderRepository->findNonFinishedByUser($user),
            'activeTab' => $request->query->getString('tab', 'orders'),
        ]);
    }

    #[Route('/order/{id}/finish', name: 'order_finish', methods: ['POST'])]
    public function finishOrder(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('account_order_finish_' . $order->getId(), $request->request->get('_token'))) {
            $order->setFinished(true);
            $em->flush();
            $this->addFlash('success', 'Commande archivée.');
        }

        return $this->redirectToRoute('account_index', ['tab' => 'orders']);
    }

    #[Route('/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em, TokenStorageInterface $tokenStorage, AdminActivityLogger $activityLogger): Response
    {
        if (!$this->isCsrfTokenValid('account_delete', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        // Journalisé avant remove() : l'historique référence l'auteur par son id, qui doit
        // encore exister en base au moment de l'INSERT (la colonne actor_id passera ensuite
        // à NULL via onDelete: SET NULL quand la ligne user sera réellement supprimée).
        $activityLogger->log(actor: $user, action: AdminActivityLog::ACTION_MEMBER_SELF_DELETED, subjectType: AdminActivityLog::SUBJECT_MEMBER, subjectId: $user->getId(), subjectLabel: $user->getPseudo());

        $em->remove($user);
        $em->flush();

        // Le User disparaît en pleine session : /logout classique rechargerait un User qui n'existe plus,
        // on déconnecte donc manuellement avant de rediriger.
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a été supprimé.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/forgot-password', name: 'forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        if (!$this->isCsrfTokenValid('account_forgot_current_password', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        // Même déconnexion manuelle que delete() : on quitte /account (ROLE_USER requis)
        // avant de rediriger vers un flow accessible aux anonymes.
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        return $this->redirectToRoute('app_forgot_password_request');
    }

    #[Route('/change-password', name: 'change_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        PasswordChangeNotifier $passwordChangeNotifier,
    ): Response {
        if (!$this->isCsrfTokenValid('account_change_password', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        $currentPassword = (string) $request->request->get('currentPassword');
        $newPassword = (string) $request->request->get('newPassword');
        $newPasswordConfirm = (string) $request->request->get('newPasswordConfirm');

        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'Mot de passe actuel incorrect.');

            return $this->redirectToRoute('account_index', ['tab' => 'password']);
        }

        if ($newPassword !== $newPasswordConfirm) {
            $this->addFlash('error', 'Les deux mots de passe ne correspondent pas.');

            return $this->redirectToRoute('account_index', ['tab' => 'password']);
        }

        if (mb_strlen($newPassword) < 12) {
            $this->addFlash('error', 'Votre nouveau mot de passe doit contenir au moins 12 caractères.');

            return $this->redirectToRoute('account_index', ['tab' => 'password']);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $em->flush();

        // Pas de mode remember_me persistant pour l'instant (cf. Phase 2 point 1) : on
        // régénère au minimum la session courante après un changement de mot de passe.
        $request->getSession()->migrate(true);

        $passwordChangeNotifier->notify($user);

        $this->addFlash('success', 'Votre mot de passe a été mis à jour.');

        return $this->redirectToRoute('account_index', ['tab' => 'password']);
    }

    #[Route('/change-pseudo', name: 'change_pseudo', methods: ['POST'])]
    public function changePseudo(Request $request, EntityManagerInterface $em, UserRepository $userRepository, AdminActivityLogger $activityLogger): Response
    {
        if (!$this->isCsrfTokenValid('account_change_pseudo', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        $pseudo = trim((string) $request->request->get('pseudo'));

        if ('' === $pseudo || mb_strlen($pseudo) < 3 || mb_strlen($pseudo) > 50) {
            $this->addFlash('error', 'Votre pseudo doit contenir entre 3 et 50 caractères.');

            return $this->redirectToRoute('account_index', ['tab' => 'pseudo']);
        }

        $existing = $userRepository->findOneBy(['pseudo' => $pseudo]);
        if ($existing && $existing->getId() !== $user->getId()) {
            $this->addFlash('error', 'Ce pseudo est déjà pris.');

            return $this->redirectToRoute('account_index', ['tab' => 'pseudo']);
        }

        $oldPseudo = $user->getPseudo();
        $user->setPseudo($pseudo);

        try {
            $em->flush();
        } catch (UniqueConstraintViolationException) {
            // Filet de sécurité contre une course entre la vérification ci-dessus et le flush.
            $this->addFlash('error', 'Ce pseudo est déjà pris.');

            return $this->redirectToRoute('account_index', ['tab' => 'pseudo']);
        }

        $activityLogger->log(actor: $user, action: AdminActivityLog::ACTION_MEMBER_PSEUDO_CHANGED, subjectType: AdminActivityLog::SUBJECT_MEMBER, subjectId: $user->getId(), subjectLabel: $pseudo, detail: sprintf('ancien pseudo : %s', $oldPseudo));

        $this->addFlash('success', 'Votre pseudo a été mis à jour.');

        return $this->redirectToRoute('account_index', ['tab' => 'pseudo']);
    }
}
