<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/categories', name: 'admin_category_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminCategoryController extends AbstractController
{
    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie créée avec succès.');
            return $this->redirectToRoute('admin_index', ['tab' => 'shop']);
        }

        return $this->render('admin/category/category_form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès.');
            return $this->redirectToRoute('admin_index', ['tab' => 'shop']);
        }

        return $this->render('admin/category/category_form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_category_' . $category->getId(), $request->request->get('_token'))) {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie et produits associés supprimés.');
        }

        return $this->redirectToRoute('admin_index', ['tab' => 'shop']);
    }
}
