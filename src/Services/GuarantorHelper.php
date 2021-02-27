<?php

namespace App\Services;

use App\Entity\Individual;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\IndividualDataRepository;

class GuarantorHelper {

    private $individualDataRepository;
    private $manager;

    public function __construct(IndividualDataRepository $individualDataRepository, EntityManagerInterface $manager){
        $this->individualDataRepository = $individualDataRepository;
        $this->manager = $manager;
    }

    public function GuarantorDisplay($garants)
    {
        $DataGarant = [];

        foreach ($garants as $garant){
            $lastname = $this->individualDataRepository->getDataByCode($garant, 'lastname');
            $firstname = $this->individualDataRepository->getDataByCode($garant, 'firstname');
            $birthDate = $this->individualDataRepository->getDataByCode($garant, 'birth_date');
            $email = $garant->getUser()->getEmail();

            $Data = [
                'id' => $garant->getId(), 
                'firstname' => $firstname->getData(), 
                'lastname' => $lastname->getData(), 
                'birth_date' => $birthDate->getData(),
                'email' => $email
            ];

            array_push($DataGarant, $Data);
        }

        return $DataGarant;
    }

    public function GuarantorActivate(Individual $garant, Individual $individual)
    {
        $verif = [];
        foreach ($individual->getIndividuals() as $individu){
            array_push($verif, $individu->getId());
        }

        if(in_array($garant->getId(), $verif)){
            return false;
        }

        $individual->addIndividual($garant);
        $this->manager->persist($individual);
        $this->manager->flush();

        return true;
    }
}