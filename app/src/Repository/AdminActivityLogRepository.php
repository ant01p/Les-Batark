<?php

namespace App\Repository;

use App\Entity\AdminActivityLog;
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
     * @param 'admin'|'member'|null $author
     *
     * @return AdminActivityLog[]
     */
    public function findFiltered(
        ?string $author,
        ?string $action,
        ?string $subjectType,
        ?string $q,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
        int $limit,
    ): array {
        $qb = $this->createQueryBuilder('a');

        return $this->applyFilters($qb, $author, $action, $subjectType, $q, $from, $to)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param 'admin'|'member'|null $author
     */
    public function countFiltered(
        ?string $author,
        ?string $action,
        ?string $subjectType,
        ?string $q,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
    ): int {
        $qb = $this->createQueryBuilder('a')->select('COUNT(a.id)');

        return (int) $this->applyFilters($qb, $author, $action, $subjectType, $q, $from, $to)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param 'admin'|'member'|null $author
     */
    private function applyFilters(
        QueryBuilder $qb,
        ?string $author,
        ?string $action,
        ?string $subjectType,
        ?string $q,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
    ): QueryBuilder {
        if ($author === 'admin') {
            $qb->andWhere('a.action NOT IN (:selfServiceActions)')->setParameter('selfServiceActions', AdminActivityLog::SELF_SERVICE_ACTIONS);
        } elseif ($author === 'member') {
            $qb->andWhere('a.action IN (:selfServiceActions)')->setParameter('selfServiceActions', AdminActivityLog::SELF_SERVICE_ACTIONS);
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
