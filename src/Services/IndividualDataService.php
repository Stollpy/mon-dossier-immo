<?php

namespace App\Services;

use App\Entity\Individual;
use App\Entity\IndividualData;
use App\Repository\ProfilesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\IndividualDataRepository;
use App\Repository\ProfilModelDataRepository;


class IndividualDataService {


    private $profileModelDataRepository;
    private $manager;
    private $profilesRepository;

    public function __construct(ProfilesRepository $profilesRepository, ProfilModelDataRepository $profileModelDataRepository, EntityManagerInterface $manager)
    {
        $this->profileModelDataRepository = $profileModelDataRepository;
        $this->manager = $manager;
        $this->profilesRepository = $profilesRepository;
    }

    public function CreateIndividualData(Individual $individual)
    {
        $individualProfiles = $individual->getProfiles();
 
         $codes = [];
        foreach ($individualProfiles as $profile){
            $code = $profile->getCode();
            $models = $this->profileModelDataRepository->getModelByProfil($code);

            foreach ($models as $model){
                array_push($codes, $model->getCode());
            }
        }
        
        foreach ($codes as $code){
            $model = $this->profileModelDataRepository->findOneBy(['code' => $code]);
            $individualData = new IndividualData();
            $individualData->setIndividual($individual);
            $individualData->setProfilModelData($model);
            $this->manager->persist($individualData);
        }       

        $this->manager->flush();

    }
}
