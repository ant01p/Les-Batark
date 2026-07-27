<?php

namespace App\Repository;

use App\Entity\Server;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Server>
 */
class ServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Server::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('images')
            ->leftJoin('s.images', 'images')
            ->orderBy('s.gameMode', 'ASC')
            ->addOrderBy('s.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllOrderedWithCreator(): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('creator')
            ->addSelect('images')
            ->leftJoin('s.createdBy', 'creator')
            ->leftJoin('s.images', 'images')
            ->orderBy('s.gameMode', 'ASC')
            ->addOrderBy('s.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Server[]
     */
    public function findRecentlyCreated(int $limit): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
