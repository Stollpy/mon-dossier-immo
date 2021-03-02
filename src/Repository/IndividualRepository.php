<?php

namespace App\Repository;

use App\Entity\Individual;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Individual|null find($id, $lockMode = null, $lockVersion = null)
 * @method Individual|null findOneBy(array $criteria, array $orderBy = null)
 * @method Individual[]    findAll()
 * @method Individual[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndividualRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Individual::class);
    }

     /**
      * @return Individual[] Returns an array of Individual objects
      */
    
    public function GarantIndividual($individual)
    {
        return $this->createQueryBuilder('i')
            ->where(':individual MEMBER OF i.individuals')
            ->setParameter('individual', $individual)
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findOneByUser($user): ?Individual
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByIdUser($id): ?Individual
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.user', 'user')
            ->addSelect('user')

            ->andWhere('user.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByInvitation($invitation): ?Individual
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.invitations', 'invit')
            ->addSelect('invit')

            ->andWhere('invit.id = :invitation')
            ->setParameter('invitation', $invitation)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
}
