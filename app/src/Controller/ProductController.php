<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\TypeRepository;
use App\Repository\QualityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(Request $request,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    ): Response {

        // Récupre toutes les catégories
        $categories = $categoryRepository->findAll();

        // Récupere lidentifiant category présent dans lURL
        $activeCategoryId = $request->query->getInt('category');

        $activeCategory = null;

        // Si une catégorie est sélectionnée dans lURL
        if ($activeCategoryId > 0) {
            $activeCategory = $categoryRepository->find($activeCategoryId);
        } else {
            // Sinon, on prend la première catégorie
            $firstCategory = reset($categories);

            if ($firstCategory !== false) {
                $activeCategory = $firstCategory;
            }
        }

        // Par défaut, aucun produit
        $products = [];

        // Si une catégorie existe, on récupère ses produits
        if ($activeCategory !== null) {
            $products = $productRepository->findBy([
                'category' => $activeCategory,
            ]);
        }

        return $this->render('product/index.html.twig', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'products' => $products,
        ]);
    }

    #[Route('/shop/{id}', name: 'product_show')]
    public function show(Product $product,TypeRepository $typeRepository,
        QualityRepository $qualityRepository
    ): Response {
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'types' => $typeRepository->findAll(),
            'qualities' => $qualityRepository->findAll(),
        ]);
    }
}