<?php

namespace App\Repository;

use App\Entity\Income;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Income|null find($id, $lockMode = null, $lockVersion = null)
 * @method Income|null findOneBy(array $criteria, array $orderBy = null)
 * @method Income[]    findAll()
 * @method Income[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Income::class);
    }

    /**
     * @return Income[] Returns an array of Income objects
     */
    
    public function findByUser($user)
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.individual', 'individual')
            ->addSelect('individual')
            ->andWhere('individual.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Income[] Returns an array of Income objects
     */
    
    public function findByUserAndYear($user, $year)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.year = :year')
            ->setParameter('year', $year)

            ->innerJoin('i.individual', 'individual')
            ->addSelect('individual')
            ->andWhere('individual.user = :user')
            ->setParameter('user', $user)

            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Income
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
