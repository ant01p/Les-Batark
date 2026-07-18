<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/orders', name: 'admin_order_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminOrderController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('admin/order/orders_index.html.twig', [
            'orders' => $orderRepository->findAllOrderedByDate(),
        ]);
    }

    #[Route('/{id}/deliver', name: 'deliver', methods: ['POST'])]
    public function deliver(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('deliver_order_' . $order->getId(), $request->request->get('_token'))) {
            $order->setDeliveredAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'Commande marquée comme livrée.');
        }

        return $this->redirectToRoute('admin_order_index');
    }
}
