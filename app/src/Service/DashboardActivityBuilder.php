<?php

namespace App\Service;

use App\Repository\EventRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;

final class DashboardActivityBuilder
{
    private const PER_SOURCE_LIMIT = 8;
    private const FEED_LIMIT = 8;

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EventRepository $eventRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @return list<array{icon: string, label: string, date: \DateTimeImmutable, link: ?string}>
     */
    public function build(): array
    {
        $entries = [];

        foreach ($this->orderRepository->findRecentlyCreated(self::PER_SOURCE_LIMIT) as $order) {
            $entries[] = [
                'icon'  => 'bi-bag-plus',
                'label' => sprintf('Nouvelle commande #%s — %s', $order->getOrderCode(), $order->getCustomerPseudo()),
                'date'  => $order->getCreatedAt(),
                'link'  => 'admin_order_index',
            ];
        }

        foreach ($this->orderRepository->findRecentlyDelivered(self::PER_SOURCE_LIMIT) as $order) {
            $entries[] = [
                'icon'  => 'bi-bag-check',
                'label' => sprintf('Commande #%s livrée', $order->getOrderCode()),
                'date'  => $order->getDeliveredAt(),
                'link'  => 'admin_order_index',
            ];
        }

        foreach ($this->eventRepository->findRecentlyCreated(self::PER_SOURCE_LIMIT) as $event) {
            $entries[] = [
                'icon'  => 'bi-calendar-event',
                'label' => sprintf('Événement créé — %s', $event->getTitle()),
                'date'  => $event->getCreatedAt(),
                'link'  => 'admin_event_index',
            ];
        }

        foreach ($this->userRepository->findRecentlyRegistered(self::PER_SOURCE_LIMIT) as $user) {
            $entries[] = [
                'icon'  => 'bi-person-plus',
                'label' => sprintf('Nouveau joueur — %s', $user->getPseudo()),
                'date'  => $user->getCreatedAt(),
                'link'  => null,
            ];
        }

        usort($entries, static fn (array $a, array $b) => $b['date'] <=> $a['date']);

        return array_slice($entries, 0, self::FEED_LIMIT);
    }
}
