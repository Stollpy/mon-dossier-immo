<?php

namespace App\Repository;

use App\Entity\IndividualDataCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IndividualDataCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndividualDataCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndividualDataCategory[]    findAll()
 * @method IndividualDataCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndividualDataCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndividualDataCategory::class);
    }

    // /**
    //  * @return IndividualDataCategory[] Returns an array of IndividualDataCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IndividualDataCategory
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
