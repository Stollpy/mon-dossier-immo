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

     /**
      * @return ProfilModelData[] Returns an array of ProfilModelData objects
      */
    
    public function getModelByProfil($code)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.profiles', 'profiles')
            ->addSelect('profiles')
            ->andWhere('profiles.code = :code')

            ->setParameter('code', $code)
            ->getQuery()
            ->getResult()
        ;
    }

        /**
      * @return ProfilModelData[] Returns an array of ProfilModelData objects
      */
    
      public function getModelByProfilAndCategory($code, $category)
      {
          return $this->createQueryBuilder('p')
              ->innerJoin('p.profiles', 'profiles')
              ->addSelect('profiles')
              ->andWhere('profiles.code = :code')
              ->setParameter('code', $code)

              ->innerJoin('p.individualDataCategory', 'category')
              ->addSelect('category')
              ->andWhere('category.code = :category')
              ->setParameter('category', $category)

              ->getQuery()
              ->getResult()
          ;
      }
    

    
    public function getModelByProfilesAndCode($profiles, string $code): ?ProfilModelData
    {
        return $this->createQueryBuilder('p')
            // ->innerJoin('p.profiles', 'profiles')
            // ->addSelect('profiles')
            
            // ->innerJoin('profiles.profilModelData', 'models')
            ->andWhere('p.profiles = :profiles')
            ->setParameter('profiles', $profiles)

            ->andWhere('p.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    /**
      * @return ProfilModelData[] Returns an array of ProfilModelData objects
      */
    
      public function getModelByIndividual($individual)
      {
          return $this->createQueryBuilder('p')
              ->innerJoin('p.IndividualData', 'data')
              ->addSelect('data')
              ->andWhere('data.individual = :individual')
  
              ->setParameter('individual', $individual)
              ->getQuery()
              ->getResult()
          ;
      }
}
