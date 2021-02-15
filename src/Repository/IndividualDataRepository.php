<?php

namespace App\Repository;

use App\Entity\IndividualData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IndividualData|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndividualData|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndividualData[]    findAll()
 * @method IndividualData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndividualDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndividualData::class);
    }

    // /**
    //  * @return IndividualData[] Returns an array of IndividualData objects
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
    public function findOneBySomeField($value): ?IndividualData
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
