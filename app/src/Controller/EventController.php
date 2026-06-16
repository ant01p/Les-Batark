<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/evenements', name: 'app_events')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAllOrderedByDate();

        $grouped = [
            'ongoing'  => [],
            'upcoming' => [],
            'finished' => [],
        ];

        foreach ($events as $event) {
            $grouped[$event->getStatus()][] = $event;
        }

        return $this->render('event/index.html.twig', [
            'grouped' => $grouped,
            'total'   => count($events),
        ]);
    }
}