<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Nombre d'utilisateurs n'ayant pas le rôle ROLE_ADMIN.
     */
    public function countNonAdmin(): int
    {
        $rows = $this->createQueryBuilder('u')
            ->select('u.roles')
            ->getQuery()
            ->getResult()
        ;

        $count = 0;
        foreach ($rows as $row) {
            if (!in_array('ROLE_ADMIN', $row['roles'], true)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Nombre d'utilisateurs détenant un rôle donné, utilisé pour protéger le dernier
     * super-administrateur (rétrogradation, suspension, suppression).
     */
    public function countByRole(string $role): int
    {
        $rows = $this->createQueryBuilder('u')
            ->select('u.roles')
            ->getQuery()
            ->getResult()
        ;

        $count = 0;
        foreach ($rows as $row) {
            if (in_array($role, $row['roles'], true)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return User[]
     */
    public function findRecentlyRegistered(int $limit): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Membres pour le panneau d'administration : recherche libre (pseudo/email) + limite SQL réelle.
     *
     * @param 'member'|'admin'|null $type
     *
     * @return User[]
     */
    public function findFilteredMembers(?string $q, ?string $type, int $limit): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->addOrderBy('u.id', 'DESC')
            ->setMaxResults($limit)
        ;

        return $this->applyMemberFilters($qb, $q, $type)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Nombre total de membres correspondant à la recherche (avant application de la limite),
     * utilisé pour savoir si "Voir plus"/"Voir moins" doivent être affichés.
     *
     * @param 'member'|'admin'|null $type
     */
    public function countFilteredMembers(?string $q, ?string $type): int
    {
        $qb = $this->createQueryBuilder('u')->select('COUNT(u.id)');

        return (int) $this->applyMemberFilters($qb, $q, $type)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param 'member'|'admin'|null $type
     */
    private function applyMemberFilters(QueryBuilder $qb, ?string $q, ?string $type): QueryBuilder
    {
        if ($q !== null && $q !== '') {
            $qb->andWhere('LOWER(u.pseudo) LIKE :q OR LOWER(u.email) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%')
            ;
        }

        if ($type === 'admin' || $type === 'member') {
            // Le rôle est stocké en JSON (ex. ["ROLE_USER","ROLE_ADMIN_EVENTS"]) : on repère un rôle
            // administratif via une recherche du nom entre guillemets, pour éviter qu'un rôle comme
            // "ROLE_ADMIN_EVENTS" ne matche par erreur un LIKE sur "ROLE_ADMIN".
            $adminChecks = [];
            foreach (array_keys(User::MANAGEABLE_ROLES) as $i => $role) {
                $adminChecks[] = "u.roles LIKE :adminRole{$i}";
                $qb->setParameter("adminRole{$i}", '%"' . $role . '"%');
            }
            $isAdmin = implode(' OR ', $adminChecks);

            $qb->andWhere($type === 'admin' ? $isAdmin : "NOT ({$isAdmin})");
        }

        return $qb;
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
