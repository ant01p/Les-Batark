<?php

namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
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
    public function index(
        Request $request,
        EventRepository $eventRepository,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $activeTab = $request->query->getString('tab', 'events');

        $activeCategoryId = $request->query->getInt('category');
        $activeCategory = $activeCategoryId > 0 ? $categoryRepository->find($activeCategoryId) : null;

        $products = $productRepository->findAllWithImages($activeCategory);

        return $this->render('admin/index.html.twig', [
            'events'         => $eventRepository->findAllOrderedByDate(),
            'products'       => $products,
            'categories'     => $categoryRepository->findAll(),
            'activeCategory' => $activeCategory,
            'activeTab'      => $activeTab,
        ]);
    }
}