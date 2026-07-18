<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function delete(Request $request, EntityManagerInterface $em, TokenStorageInterface $tokenStorage): Response
    {
        if (!$this->isCsrfTokenValid('account_delete', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();

        $em->remove($user);
        $em->flush();

        // Le User disparaît en pleine session : /logout classique rechargerait un User qui n'existe plus,
        // on déconnecte donc manuellement avant de rediriger.
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a été supprimé.');

        return $this->redirectToRoute('app_home');
    }
}
