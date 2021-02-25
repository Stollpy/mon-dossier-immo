<?php

namespace App\Repository;

use App\Entity\IncomeYear;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IncomeYear|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncomeYear|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncomeYear[]    findAll()
 * @method IncomeYear[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeYearRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncomeYear::class);
    }

    // /**
    //  * @return IncomeYear[] Returns an array of IncomeYear objects
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

    
    public function findOneByCodeAndIndividual($code, $individual): ?IncomeYear
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.code = :code')
            ->setParameter('code', $code)
            ->andWhere('i.individual = :individual')
            ->setParameter('individual', $individual)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
}
