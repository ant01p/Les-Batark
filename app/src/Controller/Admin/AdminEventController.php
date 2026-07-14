<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/events', name: 'admin_event_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminEventController extends AbstractController
{
    // ── Créer un événement ──
    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();
        $form  = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('admin_index', ['tab' => 'events']);
        }

        return $this->render('admin/event/event_form.html.twig', [
            'form'  => $form,
            'event' => $event,
            'mode'  => 'create',
        ]);
    }

    // ── Modifier un événement ──
    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Événement modifié avec succès.');
            return $this->redirectToRoute('admin_index', ['tab' => 'events']);

        }

        return $this->render('admin/event/event_form.html.twig', [
            'form'  => $form,
            'event' => $event,
            'mode'  => 'edit',
        ]);
    }

    // ── Supprimer un événement ──
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_event_' . $event->getId(), $request->request->get('_token'))) {
            $em->remove($event);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé.');
        }

        return $this->redirectToRoute('admin_index', ['tab' => 'events']);
    }
}