<?php

namespace App\Repository;

use App\Entity\IndividualData;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ProfilModelDataRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method IndividualData|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndividualData|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndividualData[]    findAll()
 * @method IndividualData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndividualDataRepository extends ServiceEntityRepository
{

    private $profileModelDataRepository;

    public function __construct(ManagerRegistry $registry, ProfilModelDataRepository $profileModelDataRepository)
    {
        parent::__construct($registry, IndividualData::class);
        $this->profileModelDataRepository = $profileModelDataRepository;

    }

    /**
     * @return IndividualData[] Returns an array of IndividualData objects
     */
    
    public function getDataByCategory($individual, string $category)
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.profilModelData', 'profil')
            ->addSelect('profil')

            ->innerJoin('profil.individualDataCategory', 'category')
            ->addSelect('category')
            ->andWhere('category.code = :category')

            ->andWhere('i.individual = :individual')
            ->setParameter('individual', $individual)
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return IndividualData[] Returns an array of IndividualData objects
     */
    
    public function getDataByIndividual($individual)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.individual = :individual')
            ->setParameter('individual', $individual)
            ->getQuery()
            ->getResult()
        ;
    }

      /**
     * @return IndividualData[] Returns an array of IndividualData objects
     */
    
    public function getDataByIndividualAndProfile($individual, $profile)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.individual = :individual')
            ->setParameter('individual', $individual)

            ->innerJoin('i.profilModelData', 'modeles')
            ->addSelect('modeles')

            ->innerJoin('modeles.profiles', 'profiles')
            ->addSelect('profiles')
            ->andWhere('profiles.code = :profiles')
            ->setParameter('profiles', $profile )

            ->getQuery()
            ->getResult()
        ;
    }

    
    public function getDataByCode($individual, string $code): ?IndividualData
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.profilModelData', 'profil')
            ->addSelect('profil')
            ->andWhere('profil.code = :code')
            ->setParameter('code', $code)

            ->andWhere('i.individual = :individual')
            ->setParameter('individual', $individual)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}