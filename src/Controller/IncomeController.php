<?php

namespace App\Controller;

use App\Form\IncomeType;
use App\Security\Access;
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
     * @param int $id
     * @param IncomeTypeRepository $IncomeTypeRepository
     * @param IncomeYearRepository $incomeRepository
     * @param IncomeHelper $incomeHelper
     * @param Access $access
     */
    public function EditIcomes($id, Access $access, IncomeTypeRepository $IncomeTypeRepository, IncomeYearRepository $incomeYearRepository, IncomeHelper $incomeHelper)
    {
        if($access->accessDashboard($id) !== true){
            $this->addFlash('error', 'Access denied !');
            return $this->redirectToRoute('home.index');
          }
        
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
     * @param int $id
     * @param Request $request
     * @param IncomeHelper $incomeHelper
     * @param Access $access
     */
    public function AddIcomes($id, Access $access, Request $request, IncomeHelper $incomeHelper)
    {
        if($access->accessDashboard($id) !== true){
            $this->addFlash('error', 'Access denied !');
            return $this->redirectToRoute('home.index');
          }

       $individual = $this->getUser()->getIndividual();
       $data = $request->get('income');
       $incomeHelper->IncomeCreate($individual, $data);
       
       $this->addFlash('success', 'Votre revenue à bien été publié.');
       return $this->redirectToRoute('income.edit', ['id' => $id]);
    }

    /**
     * @Route("mes-revenues/{id}/{code}/upload", name="income.upload_year", methods={"POST"})
     * @param int $id
     * @param string $code
     * @param Request $request
     * @param IndividualDataCategoryRepository $categoryRepository
     * @param  ProfilesRepository $profileRepository
     * @param UploadFilesHelper $uploadFilesHelper
     * @param IncomeYearRepository $incomeYearRepository
     * @param Access $access
     */
    public function uploadDocIncomeYear($id, $code, Access $access, Request $request, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository, UploadFilesHelper $uploadFilesHelper, IncomeYearRepository $incomeYearRepository)
    {
        if($access->accessDashboard($id) !== true){
            $this->addFlash('error', 'Access denied !');
            return $this->redirectToRoute('home.index');
          }

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

        $uploadFilesHelper->uploadDocPrivate($file['data'], $label['label'], $individual, $category, $profile, null, $year);

        $this->addFlash('success', 'Votre revenue pour l\'année '.$code.' à bien été téléchargé.');
        return $this->redirectToRoute('income.edit', ['id' => $this->getUser()->getId()]);
    }

    /**
     * @Route("mes-revenues/{id}/upload/{income}", name="income.upload", methods={"POST"})
     * @param int $id
     * @param int $income
     * @param Request $request
     * @param IndividualDataCategoryRepository $categoryRepository
     * @param  ProfilesRepository $profileRepository
     * @param UploadFilesHelper $uploadFilesHelper
     * @param IncomeRepository $incomeRepository
     * @param Access $access
     */
    public function uploadDocIncome($id, $income, Access $access, Request $request, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository, UploadFilesHelper $uploadFilesHelper, IncomeRepository $incomeRepository)
    {
        if($access->accessDashboard($id) !== true){
            $this->addFlash('error', 'Access denied !');
            return $this->redirectToRoute('home.index');
          }

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

        $uploadFilesHelper->uploadDocPrivate($file['data'], $incomeData->getLabel(), $individual, $category, $profile, $incomeData, null);

        $this->addFlash('success', 'Votre document associé au revenue '.$incomeData->getLabel().' à bien été téléchargé.');
        return $this->redirectToRoute('income.edit', ['id' => $this->getUser()->getId()]);
    }
}