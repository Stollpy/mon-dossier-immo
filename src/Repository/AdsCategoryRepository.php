<?php

namespace App\Repository;

use App\Entity\AdsCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdsCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdsCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdsCategory[]    findAll()
 * @method AdsCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdsCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdsCategory::class);
    }

    // /**
    //  * @return AdsCategory[] Returns an array of AdsCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AdsCategory
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
