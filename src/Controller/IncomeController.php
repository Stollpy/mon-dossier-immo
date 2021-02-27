<?php

namespace App\Controller;

use App\Form\IncomeType;
use App\Form\DocumentType;
use App\Services\IncomeHelper;
use App\Services\UploadFilesHelper;
use App\Repository\IncomeRepository;
use App\Repository\ProfilesRepository;
use App\Repository\IncomeTypeRepository;
use App\Repository\IncomeYearRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\IndividualDataCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IncomeController extends AbstractController
{
     /**
     * @Route("mes-revenues/{id}", name="income.edit", methods={"GET"})
     * @param IncomeTypeRepository $IncomeTypeRepository
     * @param IncomeYearRepository $incomeRepository
     * @param IncomeHelper $incomeHelper
     */
    public function EditIcomes(IncomeTypeRepository $IncomeTypeRepository, IncomeYearRepository $incomeYearRepository, IncomeHelper $incomeHelper)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $IncomeType = $IncomeTypeRepository->findAll();
        $typeCode = $incomeHelper->IncomeTypeForm($IncomeType);
        
        $form = $this->createForm(IncomeType::class, null, ['data_type' => $typeCode]);
        $formDocYear = $this->createForm(DocumentType::class, null, ['data_label' => 'label', 'method' => 'POST']);
        $formDocIncome = $this->createForm(DocumentType::class, null, ['method' => 'POST']);  

        $incomeYears = $incomeYearRepository->findBy(['individual' => $this->getUser()->getIndividual()]);

        $income = $incomeHelper->IncomeDisplay($incomeYears, $this->getUser()->getIndividual());
        
        
        return $this->render('user/Dashboard/information/income/index.html.twig', [
            'form' => $form->createView(),
            'incomes' => $income,
            'formDocYear' => $formDocYear->createView(),
            'formDocIncome' => $formDocIncome->createView(),
        ]);
    }

    /**
     * @Route("mes-revenues/{id}", name="income.create", methods={"POST"})
     * @param Request $request
     * @param IncomeHelper $incomeHelper
     */
    public function AddIcomes($id, Request $request, IncomeHelper $incomeHelper)
    {
       $this->denyAccessUnlessGranted('ROLE_USER');

       $individual = $this->getUser()->getIndividual();
       $data = $request->get('income');
       $incomeHelper->IncomeCreate($individual, $data);
       
       $this->addFlash('success', 'Votre revenue à bien été publié.');
       return $this->redirectToRoute('income.edit', ['id' => $id]);
    }

    /**
     * @Route("mes-revenues/{id}/{code}/upload", name="income.upload_year", methods={"POST"})
     * @param string $code
     * @param Request $request
     * @param IndividualDataCategoryRepository $categoryRepository
     * @param  ProfilesRepository $profileRepository
     * @param UploadFilesHelper $uploadFilesHelper
     * @param IncomeYearRepository $incomeYearRepository
     */
    public function uploadDocIncomeYear($code, Request $request, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository, UploadFilesHelper $uploadFilesHelper, IncomeYearRepository $incomeYearRepository)
    {
        $individual = $this->getUser()->getIndividual();
        $file = $request->files->get('document');
        $label = $request->get('document');

        $category = $categoryRepository->findOneBy(['code' => 'incomes']);
        $profile = $profileRepository->findOneBy(['code' => 'tenant']);

        $violations = $uploadFilesHelper->FileValidator($file['data']);
        if($violations->count() > 0){
            $violations = $violations[0];
            $this->request->addFlash('error', $violations->getMessage());
            return $this->redirectToRoute('income.edit', ['id' => $this->getUser()->getId()]);
        }
        
        $year = $incomeYearRepository->findOneByCodeAndIndividual($code, $individual);

        $uploadFilesHelper->uploadFilePrivate($file['data'], $label['label'], $individual, $category, $profile, null, $year);

        $this->addFlash('success', 'Votre revenue pour l\'année '.$code.' à bien été téléchargé.');
        return $this->redirectToRoute('income.edit', ['id' => $this->getUser()->getId()]);
    }

    /**
     * @Route("mes-revenues/{id}/upload/{income}", name="income.upload", methods={"POST"})
     * @param int $income
     * @param Request $request
     * @param IndividualDataCategoryRepository $categoryRepository
     * @param  ProfilesRepository $profileRepository
     * @param UploadFilesHelper $uploadFilesHelper
     * @param IncomeRepository $incomeRepository
     */
    public function uploadDocIncome($income, Request $request, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository, UploadFilesHelper $uploadFilesHelper, IncomeRepository $incomeRepository)
    {
        // dd($request->files->get('document'));
        $individual = $this->getUser()->getIndividual();

        // Récupération du document
        $file = $request->files->get('document');

        $category = $categoryRepository->findOneBy(['code' => 'incomes']);
        $profile = $profileRepository->findOneBy(['code' => 'tenant']);

        $violations = $uploadFilesHelper->FileValidator($file['data']);

        if($violations->count() > 0){
            $violations = $violations[0];
            $this->addFlash('error', $violations->getMessage());
            return $this->redirectToRoute('income.edit', ['id' => $this->getUser()->getId()]);
        }
        
        $incomeData = $incomeRepository->findOneBy(['id' => $income]);

        $uploadFilesHelper->uploadFilePrivate($file['data'], $incomeData->getLabel(), $individual, $category, $profile, $incomeData, null);

        $this->addFlash('success', 'Votre document associé au revenue '.$incomeData->getLabel().' à bien été téléchargé.');
        return $this->redirectToRoute('income.edit', ['id' => $this->getUser()->getId()]);
    }
}