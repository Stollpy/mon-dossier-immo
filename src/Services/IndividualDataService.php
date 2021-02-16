<?php

namespace App\Services;

use App\Entity\Individual;
use App\Entity\IndividualData;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\IndividualDataRepository;
use App\Repository\ProfilModelDataRepository;


class IndividualDataService {


    private $profileModelDataRepository;
    private $manager;

    public function __construct(ProfilModelDataRepository $profileModelDataRepository, EntityManagerInterface $manager)
    {
        $this->profileModelDataRepository = $profileModelDataRepository;
        $this->manager = $manager;
    }

    public function CreateIndividualData(Individual $individual, array $codes)
    {

        foreach ($codes as $code){
            $profil = $this->profileModelDataRepository->findOneBy(['code' => $code]);
            $individualData = new IndividualData();
            $individualData->setIndividual($individual);
            $individualData->setProfilModelData($profil);
            $this->manager->persist($individualData);
        }       

        $this->manager->flush();

    }
}
