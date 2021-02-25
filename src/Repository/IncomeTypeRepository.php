<?php

namespace App\Repository;

use App\Entity\IncomeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IncomeType|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncomeType|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncomeType[]    findAll()
 * @method IncomeType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncomeType::class);
    }

    // /**
    //  * @return IncomeType[] Returns an array of IncomeType objects
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
    public function findOneBySomeField($value): ?IncomeType
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
