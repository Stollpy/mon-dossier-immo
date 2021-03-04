<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * @return Document[] Returns an array of Document objects
     */

    public function findByYearAndIndividual($year, $individual)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.incomeYear = :year')
            ->setParameter('year', $year)

            ->andWhere('d.individual = :individual')
            ->setParameter('individual', $individual)

            ->getQuery()
            ->getResult()
        ;
    }

        /**
     * @return Document[] Returns an array of Document objects
     */

    public function findByCategoryAndIndividual($category, $individual)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.category = :category')
            ->setParameter('category', $category)

            ->andWhere('d.individual = :individual')
            ->setParameter('individual', $individual)

            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Document
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
