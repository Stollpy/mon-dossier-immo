<?php

namespace App\Repository;

use App\Entity\AdsPictures;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdsPictures|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdsPictures|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdsPictures[]    findAll()
 * @method AdsPictures[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdsPicturesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdsPictures::class);
    }

    // /**
    //  * @return AdsPictures[] Returns an array of AdsPictures objects
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
    public function findOneBySomeField($value): ?AdsPictures
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
