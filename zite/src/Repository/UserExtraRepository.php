<?php

namespace App\Repository;

use App\Entity\UserExtra;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserExtra>
 *
 * @method UserExtra|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserExtra|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserExtra[]    findAll()
 * @method UserExtra[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserExtraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserExtra::class);
    }

//    /**
//     * @return UserExtra[] Returns an array of UserExtra objects
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

//    public function findOneBySomeField($value): ?UserExtra
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
