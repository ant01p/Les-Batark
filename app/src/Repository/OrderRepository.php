<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @return Order[]
     */
    public function findNonFinishedByUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('oi', 'p', 't', 'q')
            ->leftJoin('o.orderItems', 'oi')
            ->leftJoin('oi.product', 'p')
            ->leftJoin('oi.type', 't')
            ->leftJoin('oi.quality', 'q')
            ->andWhere('o.user = :user')
            ->andWhere('o.finished = false')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Toutes les commandes d'un membre (fiche administrative), contrairement à
     * findNonFinishedByUser() utilisé par la page "Mon compte".
     *
     * @return Order[]
     */
    public function findByUserOrderedByDate(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('oi', 'p', 't', 'q')
            ->leftJoin('o.orderItems', 'oi')
            ->leftJoin('oi.product', 'p')
            ->leftJoin('oi.type', 't')
            ->leftJoin('oi.quality', 'q')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Order[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('oi', 'p', 't', 'q')
            ->leftJoin('o.orderItems', 'oi')
            ->leftJoin('oi.product', 'p')
            ->leftJoin('oi.type', 't')
            ->leftJoin('oi.quality', 'q')
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Utilisé avant suppression d'un membre : la présence de commandes fait basculer
     * vers une anonymisation plutôt qu'une suppression réelle.
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Somme des totaux (en centimes) de toutes les commandes.
     */
    public function sumTotal(): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COALESCE(SUM(o.total), 0)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Commandes en attente de livraison, les plus récentes en premier.
     *
     * @return Order[]
     */
    public function findPending(int $limit): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.deliveredAt IS NULL')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.deliveredAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Order[]
     */
    public function findRecentlyCreated(int $limit): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Order[]
     */
    public function findRecentlyDelivered(int $limit): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.deliveredAt IS NOT NULL')
            ->orderBy('o.deliveredAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
