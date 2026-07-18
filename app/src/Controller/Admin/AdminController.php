<?php

namespace App\Controller\Admin;

use App\Repository\EventRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\DashboardActivityBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(
        UserRepository $userRepository,
        EventRepository $eventRepository,
        OrderRepository $orderRepository,
        DashboardActivityBuilder $activityBuilder,
    ): Response {
        return $this->render('admin/index.html.twig', [
            'playerCount'       => $userRepository->countNonAdmin(),
            'eventCount'        => $eventRepository->countAll(),
            'pendingOrderCount' => $orderRepository->countPending(),
            'orderRevenue'      => $orderRepository->sumTotal(),
            'pendingOrders'     => $orderRepository->findPending(5),
            'recentActivity'    => $activityBuilder->build(),
        ]);
    }
}