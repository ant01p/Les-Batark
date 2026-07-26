<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ProductRepository;
use App\Repository\ServerRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private const NEWS_LIMIT = 20;

    #[Route('/', name: 'app_home')]
    public function index(
        UserRepository $userRepository,
        EventRepository $eventRepository,
        ProductRepository $productRepository,
        ServerRepository $serverRepository,
    ): Response {
        return $this->render('home/index.html.twig', [
            'playerCount' => $userRepository->countNonAdmin(),
            'eventCount' => $eventRepository->countAll(),
            'productCount' => $productRepository->countAll(),
            'serverCount' => $serverRepository->countAll(),
            'newsItems' => $this->buildNewsItems($eventRepository, $productRepository, $serverRepository),
        ]);
    }

    private function buildNewsItems(
        EventRepository $eventRepository,
        ProductRepository $productRepository,
        ServerRepository $serverRepository,
    ): array {
        $items = [];

        foreach ($eventRepository->findRecentlyCreated(self::NEWS_LIMIT) as $event) {
            $items[] = [
                'date' => $event->getCreatedAt(),
                'title' => 'Nouvel événement : ' . $event->getTitle(),
                'description' => 'Un nouvel événement va bientôt commencer avec de grosses récompenses ! Préparez-vous, bande de BatArk !',
                'url' => $this->generateUrl('app_events'),
            ];
        }

        foreach ($productRepository->findRecentlyCreated(self::NEWS_LIMIT) as $product) {
            $items[] = [
                'date' => $product->getCreatedAt(),
                'title' => 'Ajout boutique : ' . $product->getName(),
                'description' => 'Un nouveau produit vous attend en boutique ! Faites chauffez vos dollarks !',
                'url' => $this->generateUrl('product_show', ['id' => $product->getId()]),
            ];
        }

        foreach ($serverRepository->findRecentlyCreated(self::NEWS_LIMIT) as $server) {
            $items[] = [
                'date' => $server->getCreatedAt(),
                'title' => 'Nouveau serveur : ' . $server->getTitle(),
                'description' => sprintf(
                    'Explorez de nouveaux horizons avec l\'ajout du nouveau serveur "%s". Attention à votre stuff !',
                    $server->getTitle(),
                ),
                'url' => $this->generateUrl('app_servers'),
            ];
        }

        usort($items, fn (array $a, array $b) => $b['date'] <=> $a['date']);

        $items = \array_slice($items, 0, self::NEWS_LIMIT);

        foreach ($items as &$item) {
            $item['dateLabel'] = $this->formatRelativeDate($item['date']);
        }

        return $items;
    }

    private function formatRelativeDate(\DateTimeImmutable $date): string
    {
        $days = $date->diff(new \DateTimeImmutable())->days;

        return match (true) {
            $days === 0 => "Aujourd'hui",
            $days === 1 => 'Hier',
            default => sprintf('Il y a %d jours', $days),
        };
    }
}
