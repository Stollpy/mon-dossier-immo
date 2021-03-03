<?php

namespace App\Services;

use App\Entity\User;
use App\Entity\Individual;
use App\Entity\Invitation;
use App\Entity\IndividualData;
use App\Repository\ProfilesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\IndividualDataRepository;
use App\Repository\ProfilModelDataRepository;
use App\Repository\InvitationCategoryRepository;


class IndividualDataService {


    private $profileModelDataRepository;
    private $manager;
    private $profilesRepository;
    private $individualDataRepository;
    private $invitationCategoryRepository;

    public function __construct(ProfilesRepository $profilesRepository, ProfilModelDataRepository $profileModelDataRepository, 
    IndividualDataRepository $individualDataRepository, EntityManagerInterface $manager, InvitationCategoryRepository $invitationCategoryRepository)
    {
        $this->profileModelDataRepository = $profileModelDataRepository;
        $this->manager = $manager;
        $this->profilesRepository = $profilesRepository;
        $this->individualDataRepository = $individualDataRepository;
        $this->invitationCategoryRepository = $invitationCategoryRepository;
    }

    public function CreateIndividual(User $user, $parentProfile){
        $profile = $this->profilesRepository->findOneBy(['code' => $parentProfile]);
        $profiles = $profile->getProfiles();
        
        $individual = new Individual();
        $individual->setUser($user);
        foreach ($profiles as $profile){
            $individual->addProfile($profile);
        }
        $this->manager->persist($individual); 
        $this->manager->flush();

        return $individual;
    }

    public function CreateIndividualData(Individual $individual)
    {
        $parentProfiles = $individual->getProfiles();
 
         $codes = [];

        //  Vérifie si l'individu à déjà des données lié à des modeles de donné 
         $dataAll = $this->profileModelDataRepository->getModelByIndividual($individual);
         if($dataAll !== null ){
             foreach($dataAll as $data){
                 array_push($codes, $data->getCode());
             }
         }

        //  Inserts les codes de modeles de données qu'un profiles à besoins 
        foreach ($parentProfiles as $profile){
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

            if(!is_array($form)){
                if($code !== 'birth_date'){
                    $data = $this->individualDataRepository->getDataByCode($individual, $code);
                    $data->setData($form->get($code)->getData());
                    $this->manager->persist($data);
                }else{
                    $data = $this->individualDataRepository->getDataByCode($individual, $code);
                    $data->setData(date_format($form->get($code)->getData(), 'Y-m-d'));
                    $this->manager->persist($data);
                }
            }else{
                if($code !== 'birth_date'){
                    $data = $this->individualDataRepository->getDataByCode($individual, $code);
                    $data->setData($form[$code]);
                    $this->manager->persist($data);
                }else{
                    $data = $this->individualDataRepository->getDataByCode($individual, $code);
                    $data->setData(date_format($form[$code], 'Y-m-d'));
                    $this->manager->persist($data);
                }
            }
        }

        $this->manager->flush();
    }

    public function InvitationCreate(string $email, Individual $individual, string $code)
    {
        $category = $this->invitationCategoryRepository->findOneBy(['code' => $code]);

        $invitation = new Invitation();
        $invitation->setEmail($email);
        $invitation->setIndividual($individual);
        $invitation->setInvitationCategory($category);
        $this->manager->persist($invitation);
        $this->manager->flush();

        return $invitation;
    }

    /****************************************
     *********** DATA FIXTURES **************
     ****************************************/

    public function createIndividualFixtures(User $user, $parentProfile){
        $profiles = ['0' => 'tenant', '1' => 'seller'];
        
        $individual = new Individual();
        $individual->setUser($user);
        foreach ($profiles as $profile){
            $child = $this->profilesRepository->findOneBy(['code' => $profile]);
            $individual->addProfile($child);
        }
        $this->manager->persist($individual); 
        $this->manager->flush();

        return $individual;
    }
}
