<?php

namespace App\Repository;

use App\Entity\AdminActivityLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminActivityLog>
 */
class AdminActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminActivityLog::class);
    }

    /**
     * @return AdminActivityLog[]
     */
    public function findFiltered(
        ?int $actorId,
        ?string $action,
        ?string $subjectType,
        ?string $q,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
        int $limit,
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('actor')
            ->leftJoin('a.actor', 'actor')
        ;

        return $this->applyFilters($qb, $actorId, $action, $subjectType, $q, $from, $to)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countFiltered(
        ?int $actorId,
        ?string $action,
        ?string $subjectType,
        ?string $q,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
    ): int {
        $qb = $this->createQueryBuilder('a')->select('COUNT(a.id)');

        return (int) $this->applyFilters($qb, $actorId, $action, $subjectType, $q, $from, $to)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Admins ayant déjà au moins une ligne d'historique, pour le filtre "Administrateur".
     *
     * @return User[]
     */
    public function findDistinctActors(): array
    {
        // Racine sur User (et non AdminActivityLog) : Doctrine exige que l'alias
        // racine du FROM fasse partie du SELECT quand on sélectionne des entités,
        // hors on ne veut sélectionner que "actor", pas les lignes d'historique.
        return $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT u')
            ->from(User::class, 'u')
            ->innerJoin(AdminActivityLog::class, 'a', 'WITH', 'a.actor = u')
            ->orderBy('u.pseudo', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    private function applyFilters(
        QueryBuilder $qb,
        ?int $actorId,
        ?string $action,
        ?string $subjectType,
        ?string $q,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
    ): QueryBuilder {
        if ($actorId !== null) {
            $qb->andWhere('a.actor = :actorId')->setParameter('actorId', $actorId);
        }

        if ($action !== null) {
            $qb->andWhere('a.action = :action')->setParameter('action', $action);
        }

        if ($subjectType !== null) {
            $qb->andWhere('a.subjectType = :subjectType')->setParameter('subjectType', $subjectType);
        }

        if ($q !== null) {
            $qb->andWhere('a.subjectLabel LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        if ($from !== null) {
            $qb->andWhere('a.createdAt >= :from')->setParameter('from', $from);
        }

        if ($to !== null) {
            $qb->andWhere('a.createdAt <= :to')->setParameter('to', $to);
        }

        return $qb;
    }
}
