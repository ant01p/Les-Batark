<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        UserRepository $userRepository,
        EventRepository $eventRepository,
        ProductRepository $productRepository,
    ): Response {
        return $this->render('home/index.html.twig', [
            'playerCount' => $userRepository->countNonAdmin(),
            'eventCount' => $eventRepository->countAll(),
            'productCount' => $productRepository->countAll(),
            // Placeholder en attendant une entité Server dédiée.
            'serverCount' => 3,
        ]);
    }
}
