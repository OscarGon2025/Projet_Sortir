<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function desactiverUsers(array $userIds): int
    {
        return $this->createQueryBuilder('u')
            ->update()
            ->set('u.actif', ':inactif')
            ->where('u.id IN (:ids)')
            ->setParameter('inactif', false)
            ->setParameter('ids', $userIds)
            ->getQuery()
            ->execute();
    }

    public function supprimerUsers(array $ids): int
    {
        return $this->createQueryBuilder('u')
            ->delete()
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    public function reactiverUsers(array $ids): int
    {
        return $this->createQueryBuilder('u')
            ->update()
            ->set('u.actif', ':active')
            ->where('u.id IN (:ids)')
            ->setParameter('active', true)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
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
