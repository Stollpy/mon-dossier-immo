<?php

namespace App\Services;

use App\Entity\Individual;
use App\Repository\IncomeRepository;
use App\Repository\DocumentRepository;
use App\Repository\IndividualDataRepository;
use App\Repository\IndividualDataCategoryRepository;

class ChartHelper {

    private $documentRepository;
    private $dataCategoryRepository;
    private $dataRepository;
    private $incomeRepository;

    public function __construct(IncomeRepository $incomeRepository, IndividualDataRepository $dataRepository, DocumentRepository $documentRepository, IndividualDataCategoryRepository $dataCategoryRepository){
        $this->documentRepository = $documentRepository;
        $this->dataCategoryRepository = $dataCategoryRepository;
        $this->dataRepository = $dataRepository;
        $this->incomeRepository = $incomeRepository;
    }

    /**
     * Cette funtion sert à envoyer les données 
     * nécessaire pour calculer le graphique
     * de chaque petite étape
     *
     * @param Individual $individual
     * @param string $profile
     * @param array $options
     * @return void
     */
    public function smallChartCalculated(Individual $individual, string $profile)
    {
        $data = [];
        $dataCategory = $this->dataCategoryRepository->profileParentAndChild($profile);
        foreach($dataCategory as $category){

            $document = $this->documentRepository->findByCategoryAndIndividual($category, $individual);
            $data[$category->getCode()] = $document;

            $datasIndividuals = $this->dataRepository->getDataByCategory($individual, $category->getCode());
            foreach($datasIndividuals as $dataIndividual){
                if($dataIndividual->getData() !== null){
                    array_push($data[$category->getCode()], $dataIndividual);
                }
            }
        }
        // dd($data);
        return $data;
    }

    /**
     * Cette function sert à envoyer les données
     * nécessaire pour calculer le graphique
     * des revenus
     *
     * @param Individual $individual
     * @return void
     */
    public function smallCharteIncome(Individual $individual)
    {
        $data = [];
        $category = $this->dataCategoryRepository->findOneBy(["code" => "incomes"]);
        $document = $this->documentRepository->findByCategoryAndIndividual($category, $individual);

        $data[$category->getCode()] = $document;

        $incomeIndividuals = $this->incomeRepository->findByUser($individual->getUser());
        foreach($incomeIndividuals as $incomeIndividual){
            if($incomeIndividual !== null){
                array_push($data[$category->getCode()], $incomeIndividual);
            }
        }
        // dd($data);
        return $data;
        
    }
}