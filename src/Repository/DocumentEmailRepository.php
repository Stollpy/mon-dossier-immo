<?php

namespace App\Repository;

use App\Entity\DocumentEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DocumentEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentEmail[]    findAll()
 * @method DocumentEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentEmail::class);
    }

    // /**
    //  * @return DocumentEmail[] Returns an array of DocumentEmail objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DocumentEmail
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
