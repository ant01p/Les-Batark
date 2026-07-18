<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ImageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/products', name: 'admin_product_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminProductController extends AbstractController
{
    private const IMAGE_SUBDIR = 'imageProduct';

    #[Route('', name: 'index')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $activeCategoryId = $request->query->getInt('category');
        $activeCategory = $activeCategoryId > 0 ? $categoryRepository->find($activeCategoryId) : null;

        return $this->render('admin/product/products_index.html.twig', [
            'products'       => $productRepository->findAllWithImages($activeCategory),
            'categories'     => $categoryRepository->findAll(),
            'activeCategory' => $activeCategory,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em, ImageHandler $imageHandler): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newImagesByKey = $this->uploadNewImages($form, $product, $imageHandler);
            $this->resolveMainImage($product, $newImagesByKey, $request->request->get('mainImage'));

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit créé avec succès.');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/products_form.html.twig', [
            'form' => $form,
            'product' => $product,
            'mode' => 'create',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em, ImageHandler $imageHandler): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->deleteMarkedImages($product, $request->request->all('deleteImages'), $imageHandler, $em);
            $newImagesByKey = $this->uploadNewImages($form, $product, $imageHandler);
            $this->resolveMainImage($product, $newImagesByKey, $request->request->get('mainImage'));

            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès.');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/products_form.html.twig', [
            'form' => $form,
            'product' => $product,
            'mode' => 'edit',
        ]);
    }

    /**
     * @return array<string, ProductImage> new images indexed by their "new_{index}" key
     */
    private function uploadNewImages(FormInterface $form, Product $product, ImageHandler $imageHandler): array
    {
        $newImagesByKey = [];

        foreach ($form->get('images')->getData() ?? [] as $index => $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $filename = $imageHandler->upload($file, self::IMAGE_SUBDIR);
            $image = new ProductImage();
            $image->setFilename($filename);
            $product->addImage($image);
            $newImagesByKey['new_' . $index] = $image;
        }

        return $newImagesByKey;
    }

    /**
     * @param array<string, ProductImage> $newImagesByKey
     */
    private function resolveMainImage(Product $product, array $newImagesByKey, ?string $mainImageKey): void
    {
        $imagesByKey = $newImagesByKey;
        foreach ($product->getImages() as $image) {
            if ($image->getId() !== null) {
                $imagesByKey['existing_' . $image->getId()] = $image;
            }
        }

        foreach ($product->getImages() as $image) {
            $image->setIsMain(false);
        }

        if ($mainImageKey !== null && isset($imagesByKey[$mainImageKey])) {
            $imagesByKey[$mainImageKey]->setIsMain(true);
        } elseif (!$product->getImages()->isEmpty()) {
            $product->getImages()->first()->setIsMain(true);
        }
    }

    /**
     * @param string[] $imageIdsToDelete
     */
    private function deleteMarkedImages(Product $product, array $imageIdsToDelete, ImageHandler $imageHandler, EntityManagerInterface $em): void
    {
        foreach ($product->getImages()->toArray() as $image) {
            if (in_array((string) $image->getId(), $imageIdsToDelete, true)) {
                $imageHandler->remove($image->getFilename(), self::IMAGE_SUBDIR);
                $product->removeImage($image);
                $em->remove($image);
            }
        }
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_product_' . $product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('admin_product_index');
    }
}