<?php

namespace App\Controller\Admin;

use App\Entity\AdminActivityLog;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Service\AdminActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/categories', name: 'admin_category_')]
#[IsGranted('ROLE_ADMIN_SHOP')]
final class AdminCategoryController extends AbstractController
{
    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em, AdminActivityLogger $activityLogger): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            $activityLogger->log(actor: $this->getUser(), action: AdminActivityLog::ACTION_CATEGORY_CREATED, subjectType: AdminActivityLog::SUBJECT_CATEGORY, subjectId: $category->getId(), subjectLabel: $category->getName());
            $this->addFlash('success', 'Catégorie créée avec succès.');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/category/category_form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Category $category, Request $request, EntityManagerInterface $em, AdminActivityLogger $activityLogger): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $activityLogger->log(actor: $this->getUser(), action: AdminActivityLog::ACTION_CATEGORY_UPDATED, subjectType: AdminActivityLog::SUBJECT_CATEGORY, subjectId: $category->getId(), subjectLabel: $category->getName());
            $this->addFlash('success', 'Catégorie modifiée avec succès.');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/category/category_form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Category $category, Request $request, EntityManagerInterface $em, AdminActivityLogger $activityLogger): Response
    {
        if ($this->isCsrfTokenValid('delete_category_' . $category->getId(), $request->request->get('_token'))) {
            $id = $category->getId();
            $label = $category->getName();

            $em->remove($category);
            $em->flush();

            $activityLogger->log(actor: $this->getUser(), action: AdminActivityLog::ACTION_CATEGORY_DELETED, subjectType: AdminActivityLog::SUBJECT_CATEGORY, subjectId: $id, subjectLabel: $label);
            $this->addFlash('success', 'Catégorie et produits associés supprimés.');
        }

        return $this->redirectToRoute('admin_product_index');
    }
}
