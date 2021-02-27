<?php

namespace App\Services;

use App\Entity\Income;
use App\Entity\IncomeYear;
use App\Entity\Individual;
use App\Repository\IncomeRepository;
use App\Repository\DocumentRepository;
use App\Repository\IncomeTypeRepository;
use App\Repository\IncomeYearRepository;
use Doctrine\ORM\EntityManagerInterface;

class IncomeHelper {

    private $documentsRepository;
    private $incomeRepository;
    private $incomeYearRepository;
    private $incomeTypeRepository;
    private $manager;

    public function __construct(DocumentRepository $documentsRepository, IncomeRepository $incomeRepository, IncomeYearRepository $incomeYearRepository, 
        IncomeTypeRepository $incomeTypeRepository, EntityManagerInterface $manager){

        $this->documentsRepository = $documentsRepository;
        $this->incomeRepository = $incomeRepository;
        $this->incomeYearRepository = $incomeYearRepository;
        $this->incomeTypeRepository = $incomeTypeRepository;
        $this->manager = $manager;

    }

    public function IncomeDisplay($incomeYears, Individual $individual)
    {
        $income = [];

        foreach($incomeYears as $incomeYear){
            $documents = $this->documentsRepository->findByYearAndIndividual($incomeYear, $individual);

            $income[$incomeYear->getCode()]['document'] = $documents;
        }

        foreach($incomeYears as $incomeYear){
            $incomes = $this->incomeRepository->findBy(['incomeYear' => $incomeYear]);
            $income[$incomeYear->getCode()]['incomes'] = $incomes;

            foreach($incomes as $inc){
                if(array_key_exists('amount', $income[$incomeYear->getCode()])){
                    $income[$incomeYear->getCode()]['amount'] = $inc->getAmount() + $income[$incomeYear->getCode()]['amount'];
                }else{
                    $income[$incomeYear->getCode()]['amount'] = $inc->getAmount();
                }
            }

            $income[$incomeYear->getCode()]['amount'] = str_replace('.', ',', $income[$incomeYear->getCode()]['amount']);
        }

        return $income;
    }

    public function IncomeTypeForm($IncomeType)
    {
        $typeCode = [];
        foreach ($IncomeType as $type){
            $typeCode[$type->getLabel()] = $type->getCode();
        }

        return $typeCode;
    }

    public function IncomeCreate(Individual $individual, $data)
    {
        $year = $this->incomeYearRepository->findOneBy(['code' => $data['year'], 'individual' => $individual->getId()]);
        $type = $this->incomeTypeRepository->findOneBy(['code' => $data['type']]);
        $number = str_replace(',', '.', $data['amount']);

        $income = new Income();
        $income->setLabel($data['label']);
        $income->setAmount($number);
        if( $year === null ){
           $IncomeYear = new IncomeYear();
           $IncomeYear->setCode($data['year']);
           $IncomeYear->setIndividual($individual);
           $this->manager->persist($IncomeYear);

           $income->setIncomeYear($IncomeYear);     
        }else{
           $income->setIncomeYear($year);
        }
        $income->setType($type);
        $income->setIndividual($individual);
        $this->manager->persist($income);
        $this->manager->flush();
    }
}