<?php

namespace App\Controller\Admin;

use App\Repository\EventRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, EventRepository $eventRepository, ProductRepository $productRepository): Response
    {
        $activeTab = $request->query->getString('tab', 'events');

        return $this->render('admin/index.html.twig', [
            'events'    => $eventRepository->findAllOrderedByDate(),
            'products'  => $productRepository->findAll(),
            'activeTab' => $activeTab,
        ]);
    }
}