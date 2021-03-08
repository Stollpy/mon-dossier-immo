<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\Access;
use App\Form\DocumentType;
use App\Form\IdentityType;
use App\Form\InvitationType;
use App\Services\ChartHelper;
use App\Services\MailService;
use App\Services\UploadFilesHelper;
use App\Repository\ProfilesRepository;
use App\Services\IndividualDataService;
use App\Repository\IndividualDataRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\IndividualDataCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TenantController extends AbstractController
{
   /**
      * @Route("mes-informations-locataire/{id}", name="tenant.edit")
      * @param Request $request
      * @param IndividualDataService $individualDataService
      * @param IndividualDataRepository $individualDataRepository
      * @param User $user
      * @param Access $access
      * @param ChartHelper $chart
      */
      public function EditInformations(ChartHelper $chart, Access $access, Request $request, User $user, IndividualDataService $individualDataService, IndividualDataRepository $individualDataRepository)
      {
        if($access->accessDashboard($user->getId()) !== true){
          $this->addFlash('error', 'Access refuse !');
          return $this->redirectToRoute('home.index');
        }

          $individual = $user->getIndividual();
          $datas = $individualDataRepository->getDataByIndividualAndProfile($individual, 'tenant');

          $form = $this->createForm(IdentityType::class, null, ['data_profile' => 'tenant' ,'data_category' => 'identity']);
          $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()){

            $individualDataService->insertIndividualData($individual, $form, 'tenant', 'identity');

            $id = $user->getId();
            $this->addFlash('success', 'Vos données ont bien été modifié');
            return $this->redirectToRoute('tenant.edit', ['id' => $id]);
          }

          $formDoc = $this->createForm(DocumentType::class, null, ['data_label' => 'label', 'action' => $this->generateUrl('tenant.upload', ['id' => $user->getId()]), 'method' => 'POST']);
          $formInvitation = $this->createForm(InvitationType::class, null, ['action' => $this->generateUrl('tenant.invitation', ['id' => $user->getId()]), 'method' => 'POST']);
          
          $formDomiciliation = $this->createForm(IdentityType::class, null, ['action' => $this->generateUrl('tenant.domiciliation', ['id' => $user->getId()]),'data_profile' => 'tenant', 'data_category' => 'domiciliation', 'method' => 'POST']);
          $formDomiciliationUpload = $this->createForm(DocumentType::class, null, ['data_label' => 'label', 'action' => $this->generateUrl('tenant.domiciliation-upload', ['id' => $user->getId()]), 'method' => 'POST']);
         
          $chartDirectory = $chart->smallChartCalculated($individual, 'tenant');
          $chartIncome = $chart->smallCharteIncome($individual);

          return $this->render('user/Dashboard/information/identity/tenant/index.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
            'formDoc' => $formDoc->createView(),
            'formInvitation' => $formInvitation->createView(),
            'formDomiciliationUpload' => $formDomiciliationUpload->createView(),
            'formDomiciliation' => $formDomiciliation->createView(),
            'chartDirectory' => $chartDirectory,
            'chartIncome' => $chartIncome['incomes'],
          ]);
      }


    /**
    * @Route("mes-informations-locataire/{id}/create-invitation", name="tenant.invitation", methods={"POST"})
    * @param int $id
    * @param Request $request
    * @param MailService $mail
    * @param IndividualDataService $dataService
    * @param Access $access
    */
    public function createInvitation($id, Access $access, Request $request, MailService $mail, IndividualDataService $dataService)
    {
      if($access->accessDashboard($id) !== true){
        $this->addFlash('error', 'Access refuse !');
        return $this->redirectToRoute('home.index');
      }
        $req = $request->get('invitation');
        $email = $req['email'];
        $ref = $req['ref'];

        $individual = $this->getUser()->getIndividual();
        $invitation = $dataService->InvitationCreate($email, $individual, 'directory_tenant');

        $subject = 'Dossier de location pour le bien '.$ref;
        $template = 'mail_template/Dossier-location/index.html.twig';
        $mail->PostMail($email, $subject, $template, ['ref' => $ref, 'invitation' => $invitation->getId()]);

        $this->addFlash('success', 'Votre invitation de dossier à bien été envoyé');
        return $this->redirectToRoute('tenant.edit', ['id' => $this->getUser()->getId()]);
    }

     /**
       * @Route("mes-informations-locataire/{id}/upload/identity", name="tenant.upload", methods={"POST"})
       * @param int $id
       * @param Request $request
       * @param UploadFilesHelper $uploadFilesHelper
       * @param User $user
       * @param IndividualDataCategoryRepository $categroyRepository
       * @param ProfilesRepository $profileRepository
       * @param Access $access
       */
      public function tenantUplodadDocument($id, Access $access, Request $request, User $user, UploadFilesHelper $uploadFilesHelper, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository)
      {
        if($access->accessDashboard($id) !== true){
          $this->addFlash('error', 'Access refuse !');
          return $this->redirectToRoute('home.index');
        }

        $individual = $user->getIndividual();
        $file = $request->files->get('document');
        $label = $request->get('document');

        $category = $categoryRepository->findOneBy(['code' => 'identity']);
        $profile = $profileRepository->findOneBy(['code' => 'tenant']);

        $id = $user->getId();

        $violations = $uploadFilesHelper->FileValidator($file['data']);
        if($violations->count() > 0){
            $violations = $violations[0];
            $this->addFlash('error', $violations->getMessage());
            return $this->redirectToRoute('seller.edit', ['id' => $id]);
        }
        
        $uploadFilesHelper->uploadDocPrivate($file['data'], $label['label'], $individual, $category, $profile);

        $this->addFlash('success', 'Votre documents à bien été téléchargé ! Vous pouvez le retouver dans votre rubrique "Mes documents".');
        return $this->redirectToRoute('document.edit', ['id' => $id]);

      }

      /**
       * @Route("mes-informations-locataire/{id}/domiciliation", name="tenant.domiciliation", methods={"POST"})
       * @param int $id
       * @param Request $request
       * @param Access $access
       * @param IndividualDataService $individualDataService
       */
      public function editDomiciliation($id, Access $access, Request $request, IndividualDataService $individualDataService)
      {
        if($access->accessDashboard($id) !== true){
          $this->addFlash('error', 'Access refuse !');
          return $this->redirectToRoute('home.index');
        }
          $data = $request->get('identity');
          $individualDataService->insertIndividualData($this->getUser()->getIndividual(), $data, 'tenant', 'domiciliation');

          $this->addFlash('success', 'Vos données ont bien été modifié');
          return $this->redirectToRoute('tenant.edit', ['id' => $id]);
      }

       /**
       * @Route("mes-informations-locataire/{id}/domiciliation-upload", name="tenant.domiciliation-upload", methods={"POST"})
       * @param int $id
       * @param Access $access
       * @param Request $request
       * @param User $user
       * @param IndividualDataCategoryRepository $categoryRepository
       * @param ProfilesRepository $profileRepository
       * @param UploadFilesHelper $uploadFilesHelper
       */
      public function uploadDomiciliation($id, Access $access, Request $request, User $user, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository, UploadFilesHelper $uploadFilesHelper)
      {
        if($access->accessDashboard($id) !== true){
          $this->addFlash('error', 'Access refuse !');
          return $this->redirectToRoute('home.index');
        }

        $individual = $user->getIndividual();
        $file = $request->files->get('document');
        $label = $request->get('document');

        $category = $categoryRepository->findOneBy(['code' => 'domiciliation']);
        $profile = $profileRepository->findOneBy(['code' => 'tenant']);

        $id = $user->getId();

        $violations = $uploadFilesHelper->FileValidator($file['data']);
        if($violations->count() > 0){
            $violations = $violations[0];
            $this->addFlash('error', $violations->getMessage());
            return $this->redirectToRoute('seller.edit', ['id' => $id]);
        }
  
        $uploadFilesHelper->uploadDocPrivate($file['data'], $label['label'], $individual, $category, $profile);

        $this->addFlash('success', 'Votre documents à bien été téléchargé ! Vous pouvez le retouver dans votre rubrique "Mes documents".');
        return $this->redirectToRoute('document.edit', ['id' => $id]);

      }
}