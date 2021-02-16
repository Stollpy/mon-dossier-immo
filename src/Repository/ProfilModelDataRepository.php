<?php

namespace App\Repository;

use App\Entity\ProfilModelData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfilModelData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfilModelData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfilModelData[]    findAll()
 * @method ProfilModelData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfilModelDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfilModelData::class);
    }

    // /**
    //  * @return ProfilModelData[] Returns an array of ProfilModelData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProfilModelData
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
