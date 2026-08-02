<?php

namespace App\Controller\Admin;

use App\Entity\AdminActivityLog;
use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\AdminActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/events', name: 'admin_event_')]
#[IsGranted('ROLE_ADMIN_EVENTS')]
final class AdminEventController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('admin/event/events_index.html.twig', [
            'events' => $eventRepository->findAllOrderedByDate(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em, AdminActivityLogger $activityLogger): Response
    {
        $event = new Event();

        /** @var User $user */
        $user = $this->getUser();
        $event->setCreatedBy($user);

        $form  = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();

            $activityLogger->log(actor: $user, action: AdminActivityLog::ACTION_EVENT_CREATED, subjectType: AdminActivityLog::SUBJECT_EVENT, subjectId: $event->getId(), subjectLabel: $event->getTitle());
            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('admin_event_index');
        }

        return $this->render('admin/event/event_form.html.twig', [
            'form'  => $form,
            'event' => $event,
            'mode'  => 'create',
        ]);
    }

    // ── Modifier un événement ──
    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Event $event, Request $request, EntityManagerInterface $em, AdminActivityLogger $activityLogger): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $activityLogger->log(actor: $this->getUser(), action: AdminActivityLog::ACTION_EVENT_UPDATED, subjectType: AdminActivityLog::SUBJECT_EVENT, subjectId: $event->getId(), subjectLabel: $event->getTitle());
            $this->addFlash('success', 'Événement modifié avec succès.');
            return $this->redirectToRoute('admin_event_index');

        }

        return $this->render('admin/event/event_form.html.twig', [
            'form'  => $form,
            'event' => $event,
            'mode'  => 'edit',
        ]);
    }

    // ── Supprimer un événement ──
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Event $event, Request $request, EntityManagerInterface $em, AdminActivityLogger $activityLogger): Response
    {
        if ($this->isCsrfTokenValid('delete_event_' . $event->getId(), $request->request->get('_token'))) {
            $id = $event->getId();
            $label = $event->getTitle();

            $em->remove($event);
            $em->flush();

            $activityLogger->log(actor: $this->getUser(), action: AdminActivityLog::ACTION_EVENT_DELETED, subjectType: AdminActivityLog::SUBJECT_EVENT, subjectId: $id, subjectLabel: $label);
            $this->addFlash('success', 'Événement supprimé.');
        }

        return $this->redirectToRoute('admin_event_index');
    }
}