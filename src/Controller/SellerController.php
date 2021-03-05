<?php

namespace App\Controller;

use App\Entity\Ads;
use App\Entity\User;
use App\Form\AdsType;
use App\Security\Access;
use App\Entity\Individual;
use App\Form\DocumentType;
use App\Form\IdentityType;
use App\Services\MailService;
use App\Form\CheckDirectoryType;
use App\Repository\AdsRepository;
use App\Services\UploadFilesHelper;
use App\Repository\DocumentRepository;
use App\Repository\ProfilesRepository;
use App\Services\IndividualDataService;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\IndividualDataRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\IndividualDataCategoryRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SellerController extends AbstractController
{
     /**
      * @Route("mes-informations-vendeur/{id}", name="seller.edit")
      * @param int $id
      * @param Request $request
      * @param IndividualDataService $individualDataService
      * @param IndividualDataRepository $individualDataRepository
      * @param Access $access
      * @param User $user
      */
      public function EditInformationsSeller($id, Access $access, Request $request, User $user, IndividualDataService $individualDataService, IndividualDataRepository $individualDataRepository)
      {
        if($access->accessDashboard($id) !== true){
          $this->addFlash('error', 'Access refuse !');
          return $this->redirectToRoute('home.index');
        }
          $individual = $user->getIndividual();

          $datas = $individualDataRepository->getDataByIndividualAndProfile($individual, 'seller');

          $form = $this->createForm(IdentityType::class, null, ['data_profile' => 'seller' ,'data_category' => 'identity']);
          $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()){

            $individual = $user->getIndividual();
            $individualDataService->insertIndividualData($individual, $form, 'seller', 'identity');

            $id = $user->getId();
            $this->addFlash('success', 'Vos données ont bien été modifié');
            return $this->redirectToRoute('seller.edit', ['id' => $id]);

          }

          $formDoc = $this->createForm(DocumentType::class, null, ['data_label' => 'label', 'action' => $this->generateUrl('seller.upload', ['id' => $user->getId()]), 'method' => 'POST']);

          return $this->render('user/Dashboard/information/identity/seller/index.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
            'formDoc' => $formDoc->createView(),
          ]);
      }


      /**
       * @Route("mes-informations-vendeur/{id}/upload", name="seller.upload", methods={"POST"})
       * @param int $id
       * @param Request $request
       * @param UploadFilesHelper $uploadFilesHelper
       * @param User $user
       * @param IndividualDataCategoryRepository $categroyRepository
       * @param ProfilesRepository $profileRepository
       * @param Access $access
       */
      public function sellerUplodadDocument($id, Access $access, Request $request, User $user, UploadFilesHelper $uploadFilesHelper, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository)
      {
        if($access->accessDashboard($id) !== true){
          $this->addFlash('error', 'Access refuse !');
          return $this->redirectToRoute('home.index');
        }
        $individual = $user->getIndividual();

        // Récupération du document
        $req = $request->files->get('document');
        $file = $req['data'];

        // Récupération du titre du document
        $req = $request->get('document');
        $label = $req['label'];

        $category = $categoryRepository->findOneBy(['code' => 'identity']);
        $profile = $profileRepository->findOneBy(['code' => 'seller']);

        $id = $user->getId();

        $violations = $uploadFilesHelper->FileValidator($file);
        if($violations->count() > 0){
            $violations = $violations[0];
            $this->addFlash('error', $violations->getMessage());
            return $this->redirectToRoute('seller.edit', ['id' => $id]);
        }
        
        $uploadFilesHelper->uploadFilePrivate($file, $label, $individual, $category, $profile);

        $this->addFlash('success', 'Votre documents à bien été téléchargé ! Vous pouvez le retouver dans votre rubrique "Mes documents".');
        return $this->redirectToRoute('document.edit', ['id' => $id]);

      }

       /**
       * @Route("dossier-locataire/{invitation}/check", name="seller.directory_tenant_check_email", methods={"GET"})
       * @param int $invitation
       * @param SessionInterface $session
       * @param InvitationRepository $invitationRepository
       * @param MailService $mail
       */
      public function checkEmailDirectoryTenant($invitation, SessionInterface $session, InvitationRepository $invitationRepository, MailService $mail)
      {
        $invit = $invitationRepository->findOneBy(['id' => $invitation]);

        if(!empty($session->get($invit->getIndividual()->getId()))){
          $session->remove($invit->getIndividual()->getId());
        }
            
        $codeBrut = mt_rand(1000, 9999);
        $session->get('ValidCode', []);
        $session->set('ValidCode',[number_format($codeBrut, 0,'', '')]);
            
        $email = $invit->getEmail();
        $subject = 'Code de sécuirté';
        $template = 'mail_template/Dossier-location/check/index.html.twig';
        $mail->PostMail($email, $subject, $template, ['code' => $codeBrut]);

        $form = $this->createForm(CheckDirectoryType::class, null, ['action' => $this->generateUrl('seller.directory_tenant_check_code', ['invitation' => $invitation])]);

        return $this->render('user/dossier-locataire/check/index.html.twig', [
          'form' => $form->createView(),
        ]);
      }

      /**
       * @Route("dossier-locataire/{invitation}/check", name="seller.directory_tenant_check_code", methods={"POST"})
       * @param SessionInterface $session
       * @param Request $request
       * @param InvitationRepository $invitationRepository
       * @param TokenGeneratorInterface $tokenInterface
       */
      public function checkCodeDirectoryTenant($invitation, Request $request, SessionInterface $session, InvitationRepository $invitationRepository, TokenGeneratorInterface $tokenInterface)
      {
        $invit = $invitationRepository->findOneBy(['id' => $invitation]);
        $data = $request->get('check_directory');
        $code = $session->get('ValidCode');

        if(!$data['number']){
          $this->addFlash('error', 'Vous devez précisez un code !');
          return $this->redirectToRoute('seller.directory_tenant_check_email', ['invitation' => $invitation]);
        }
        
        $number = number_format($data['number'], 0, '', '');
        if($number == $code[0]){
          
          $session->remove('ValidCode');
          $individual = $invit->getIndividual();
          $session->get($individual->getId(), []);
          $token = $tokenInterface->generateToken();
          $session->set($individual->getId(), [$token]);

          return $this->redirectToRoute('seller.directory_tenant', ['id' => $individual->getId(), 'token' => $token]);
        }else{
          $this->addFlash('error', 'mauvais code');
          return $this->redirectToRoute('seller.directory_tenant_check_email', ['invitation' => $invitation]);
        }
      }

      /**
       * @Route("dossier-locataire/{id}/{token}", name="seller.directory_tenant")
       * @param Individual $individual
       * @param DocumentRepository $documentRepository
       * @param IndividualDataRepository $individualDataRepository
       * @param SessionInterface $session
       */
      public function displayDirectoryTenant($token, Individual $individual, DocumentRepository $documentRepository, IndividualDataRepository $individualDataRepository, SessionInterface $session)
      {
        $code = $session->get($individual->getId());
        if(!empty($session->get($individual->getId())) && $code[0] == $token){
            $documents = $documentRepository->findBy(['individual' => $individual]);
            $datas = $individualDataRepository->getDataByIndividualAndProfile($individual,'tenant');
                
            return $this->render('user/dossier-locataire/index.html.twig', [
                'documents' => $documents,
                'datas' => $datas,
                'token' => $token
            ]);
        }
        $this->addFlash('error', 'Vous n\'êtiez pas autorisé à être sur cette page.');
        return $this->redirectToRoute('home.index');
      }
      
    /**
     * @Route("/mes-annonces/{id}", name="seller.ads")
     * @param int $id
     * @param Access $access
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param AdsRepository $adsRepository
     */
    public function displayAds($id, EntityManagerInterface $manager, Request $request, AdsRepository $adsRepository, Access $access)
    {
      if($access->accessDashboard($id) !== true){
        $this->addFlash('error', 'Access refuse !');
        return $this->redirectToRoute('home.index');
      }

      $individual = $this->getUser()->getIndividual();
      $adsIndividual = $adsRepository->findBy(['individual' => $individual]);

      $form = $this->createForm(AdsType::class, null, []);
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()){     

        $ads = new Ads();
        $ads->setTitre($form->get('titre')->getData());
        $ads->setContent($form->get('content')->getData());
        $ads->setIndividual($individual);

        $manager->persist($ads);
        $manager->flush();
        $this->addFlash('success', 'Votre annonce à bien été publié !');
        return $this->redirectToRoute('seller.ads', ['id' => $id]);
      }

      return $this->render('user/Dashboard/information/Ads/index.html.twig', [
        'form' => $form->createView(),
        'ads' => $adsIndividual,
      ]);
    }
}
