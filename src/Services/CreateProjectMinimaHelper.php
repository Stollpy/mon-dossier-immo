<?php

namespace App\Services;

use App\Entity\Profiles;
use App\Entity\IncomeType;
use App\Entity\ProfilModelData;
use App\Entity\InvitationCategory;
use App\Entity\IndividualDataCategory;
use App\Repository\ProfilesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProfilModelDataRepository;
use App\Repository\IndividualDataCategoryRepository;

class CreateProjectMinimaHelper {

    private $manager;
    private $dataCategoryRepository;
    private $profilesRepository;
    private $modelDataRepository;

    public function __construct(EntityManagerInterface $manager, IndividualDataCategoryRepository $dataCategoryRepository, ProfilesRepository $profilesRepository, ProfilModelDataRepository $modelDataRepository)
    {
        $this->manager = $manager;
        $this->dataCategoryRepository = $dataCategoryRepository;
        $this->profilesRepository = $profilesRepository;
        $this->modelDataRepository = $modelDataRepository;
    }
    
    public function createProjectMinima()
    {
        $this->createDataCategory();
        $this->createModelData();
        $this->createInvitationCategory();
        $this->createIncomeType();
        $this->createProfil();
    }

    public function createDataCategory()
    {
        $dataCategory = [
            "0" => [
                'label' => 'Identité',
                'code' => 'identity'
            ],
            "1" => [
                'label' => 'Revenues',
                'code' => 'incomes'
            ],
            "3" => [
                'label' => 'Domiciliation',
                'code' => 'domiciliation'
            ]
        ];

        foreach ($dataCategory as $data){
            $category = new IndividualDataCategory;
            $category->setLabel($data['label']);
            $category->setCode($data['code']);
            $this->manager->persist($category);
        }
        $this->manager->flush();
    }

    public function createModelData()
    {
        $modelData = $this->dataProfilModelData();
        foreach ($modelData as $data){
            $category = $this->dataCategoryRepository->findOneBy(['code' => $data['category']]);
            $profilModelData = new ProfilModelData;
            $profilModelData->setLabel($data['label']);
            $profilModelData->setCode($data['code']);
            $profilModelData->setType($data['type']);
            $profilModelData->setIndividualDataCategory($category);
            $this->manager->persist($profilModelData);
        }
        $this->manager->flush();
    }

    public function createInvitationCategory()
    {
        $invitationCategory = [
            '0' => [
                'code' => 'guarantor'
            ],
            '1' => [
                'code' => 'directory_tenant'
            ],
        ];
        foreach ($invitationCategory as $category){
            $invitCategory = new InvitationCategory;
            $invitCategory->setCode($category['code']);
            $this->manager->persist($invitCategory);
        }
        $this->manager->flush();
    }

    public function createIncomeType()
    {
        $incomeType = [
            '0' => [
                'label' => 'Fiche de paie',
                'code' => 'payslip'
            ],
            '1' => [
                'label' => 'Subvention',
                'code' => 'subsidies'
            ],
        ];
        foreach ($incomeType as $incomes)
        {
            $type = new IncomeType;
            $type->setLabel($incomes['label']);
            $type->setCode($incomes['code']);
            $this->manager->persist($type);
        }
        $this->manager->flush();
    }

    public function createProfil()
    {
        $profils = $this->dataProfils();
       
        foreach ($profils['parent'] as $profil){
            $parent = new Profiles;
            $parent->setLabel($profil['label']);
            $parent->setCode($profil['code']);
            $this->manager->persist($parent);
        }
        $this->manager->flush();
        
        foreach ($profils['child'] as $profil){
            $parent = $this->profilesRepository->findOneBy(['code' => $profil['parent']]);
            $child = new Profiles;
            $child->setLabel($profil['label']);
            $child->setCode($profil['code']);
            $child->setParentProfile($parent);

            foreach ($profil['modelData'] as $modelData){
                $model = $this->modelDataRepository->findOneBy(['code' => $modelData]);
                $child->addProfileModelData($model);
            }
            $this->manager->persist($child);
        }
        $this->manager->flush();
    }

    public function dataProfils(){
        return [
            'parent' => [
                '0' => [
                    'label' => 'Je suis un particulier',
                    'code' => 'individual'
                ],
            ],
            'child' => [
                '0' => [
                    'label' => 'Locataire',
                    'code' => 'tenant',
                    'parent' => 'individual',
                    'modelData' => [
                        '0' => 'firstname',
                        '1' => 'lastname',
                        '2' => 'birth_date',
                    ],
                ],
                '1' => [
                    'label' => 'Vendeur',
                    'code' => 'seller',
                    'parent' => 'individual',
                    'modelData' => [
                        '0' => 'firstname',
                        '1' => 'lastname',
                        '2' => 'birth_date',
                        '3' => 'tel'
                    ],
                ],
            ],
        ];
    }

    public function dataProfilModelData()
    {
        return [
            '0' => [
                'label' => 'Prénom',
                'code' => 'firstname',
                'type' => 'text',
                'category' => 'identity'
            ],
            '1' => [
                'label' => 'Nom',
                'code' => 'lastname',
                'type' => 'text',
                'category' => 'identity'
            ],
            '2' => [
                'label' => 'Date de naissance',
                'code' => 'birth_date',
                'type' => 'date',
                'category' => 'identity'
            ],
            '3' => [
                'label' => 'Téléphone',
                'code' => 'tel',
                'type' => 'tel',
                'category' => 'identity'
            ]
        ];
    }

}