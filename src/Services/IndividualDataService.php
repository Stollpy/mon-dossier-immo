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
    private $individualDataRepository;

    public function __construct(ProfilesRepository $profilesRepository, ProfilModelDataRepository $profileModelDataRepository, 
    IndividualDataRepository $individualDataRepository, EntityManagerInterface $manager)
    {
        $this->profileModelDataRepository = $profileModelDataRepository;
        $this->manager = $manager;
        $this->profilesRepository = $profilesRepository;
        $this->individualDataRepository = $individualDataRepository;
    }

    public function CreateIndividual($user, $profiles){

            $individual = new Individual();
            $individual->setUser($user);
            foreach ($profiles->getProfiles() as $profile){
                $individual->addProfile($profile);
            }
            $this->manager->persist($individual); 
            $this->manager->flush();

            $this->CreateIndividualData($individual);
    }

    public function CreateIndividualData(Individual $individual)
    {
        $individualProfiles = $individual->getProfiles();
 
         $codes = [];

        //  Vérifie si l'individu à déjà des données lié à des modeles de donné 
         $dataAll = $this->profileModelDataRepository->getModelByIndividual($individual);
         if($dataAll !== null ){
             foreach($dataAll as $data){
                 array_push($codes, $data->getCode());
             }
         }

        //  Inserts les codes de modeles de données qu'un profiles à besoins 
        foreach ($individualProfiles as $profile){
            $code = $profile->getCode();
            $models = $this->profileModelDataRepository->getModelByProfil($code);

            foreach ($models as $model){
                if(!in_array($model->getCode(), $codes)){
                    array_push($codes, $model->getCode());
                }
            }
        }
        // Insert les données qu'un profiles a besoins 
        foreach ($codes as $code){
            $model = $this->profileModelDataRepository->findOneBy(['code' => $code]);
            $individualData = new IndividualData();
            $individualData->setIndividual($individual);
            $individualData->setProfilModelData($model);
            $this->manager->persist($individualData);
        }       

        $this->manager->flush();

    }

    public function insertIndividualData($individual, $form, $profile, $category)
    {
        $models = $this->profileModelDataRepository->getModelByProfilAndCategory($profile, $category);
        
        foreach ($models as $model){
            $code = $model->getCode();

            if($code !== 'birth_date'){
                $data = $this->individualDataRepository->getDataByCode($individual, $code);
                $data->setData($form->get($code)->getData());
                $this->manager->persist($data);
            }else{
                $data = $this->individualDataRepository->getDataByCode($individual, $code);
                $data->setData(date_format($form->get($code)->getData(), 'Y-m-d'));
                $this->manager->persist($data);
            }
        }

        $this->manager->flush();
    }
}
