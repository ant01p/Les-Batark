<?php

namespace App\Controller\Admin;

use App\Repository\EventRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\DashboardActivityBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Le tableau de bord est le hub commun : accessible dès qu'on détient au moins une
// permission administrative (chaque section y ajoute ensuite sa propre vérification).
#[Route('/admin', name: 'admin_')]
#[IsGranted(new Expression(
    "is_granted('ROLE_ADMIN_EVENTS') or is_granted('ROLE_ADMIN_SHOP') or is_granted('ROLE_ADMIN_ORDERS')".
    " or is_granted('ROLE_ADMIN_SERVERS') or is_granted('ROLE_ADMIN_RULES') or is_granted('ROLE_ADMIN_MEMBERS')"
))]
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