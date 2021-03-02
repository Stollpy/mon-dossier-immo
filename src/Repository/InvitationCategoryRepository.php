<?php

namespace App\Repository;

use App\Entity\InvitationCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InvitationCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvitationCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvitationCategory[]    findAll()
 * @method InvitationCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvitationCategory::class);
    }

    // /**
    //  * @return InvitationCategory[] Returns an array of InvitationCategory objects
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
    public function findOneBySomeField($value): ?InvitationCategory
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
