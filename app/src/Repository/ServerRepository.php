<?php

namespace App\Repository;

use App\Entity\Server;
use App\Entity\User;
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

    /**
     * Server::$createdBy est non-nullable (pas d'onDelete SET NULL) : un membre ayant créé
     * au moins un serveur ne peut donc jamais être supprimé réellement, seulement anonymisé.
     */
    public function countByCreator(User $user): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.createdBy = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
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
