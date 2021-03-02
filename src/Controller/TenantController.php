<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DocumentType;
use App\Form\IdentityType;
use App\Form\InvitationType;
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
      */
      public function EditInformations(Request $request, User $user, IndividualDataService $individualDataService, IndividualDataRepository $individualDataRepository)
      {
        $this->denyAccessUnlessGranted('ROLE_USER');

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
          
          return $this->render('user/Dashboard/information/identity/index.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
            'formDoc' => $formDoc->createView(),
            'formInvitation' => $formInvitation->createView(),
          ]);
      }


    /**
    * @Route("mes-informations-locataire/{id}/create-invitation", name="tenant.invitation", methods={"POST"})
    * @param Request $request
    * @param MailService $mail
    * @param IndividualDataService $dataService
    */
    public function createInvitation(Request $request, MailService $mail, IndividualDataService $dataService)
    {
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
       * @Route("mes-informations-locataire/{id}/upload", name="tenant.upload", methods={"POST"})
       * @param Request $request
       * @param UploadFilesHelper $uploadFilesHelper
       * @param User $user
       * @param IndividualDataCategoryRepository $categroyRepository
       * @param ProfilesRepository $profileRepository
       */
      public function tenantUplodadDocument(Request $request, User $user, UploadFilesHelper $uploadFilesHelper, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository)
      {
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
        
        $uploadFilesHelper->uploadFilePrivate($file['data'], $label['label'], $individual, $category, $profile);

        $this->addFlash('success', 'Votre documents à bien été téléchargé ! Vous pouvez le retouver dans votre rubrique "Mes documents".');
        return $this->redirectToRoute('document.edit', ['id' => $id]);

      }
}