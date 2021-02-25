<?php

namespace App\Controller;


use App\Entity\User;
use App\Entity\Income;
use App\Form\UserType;
use App\Entity\Document;
use App\Form\IncomeType;
use App\Entity\IncomeYear;
use App\Entity\Individual;
use App\Entity\Invitation;
use App\Form\DocumentType;
use App\Form\EditUserType;
use App\Form\IdentityType;
use App\Form\InvitationType;
use App\Form\CreateGarantType;
use App\Form\CheckDirectoryType;
use App\Form\EditUserPasswordType;
use App\Form\PasswordRecoveryType;
use App\Repository\UserRepository;
use App\Form\PasswordResettingType;
use App\Services\UploadFilesHelper;
use App\Repository\IncomeRepository;
use App\Repository\DocumentRepository;
use App\Repository\ProfilesRepository;
use App\Services\IndividualDataService;
use App\Repository\IncomeTypeRepository;
use App\Repository\IncomeYearRepository;
use App\Repository\IndividualRepository;
use App\Repository\InvitationRepository;
use App\Security\LoginFormAuthenficator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\UriSigner;
use App\Repository\IndividualDataRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\Validator\Constraints\File;
use App\Repository\IndividualDataCategoryRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;


class UserController extends AbstractController
{
    private $manager;
    private $mailer;
    private $userRepository;
    private $token;

    public function __construct(EntityManagerInterface $manager, MailerInterface $mailer, 
        UserRepository $userRepository, TokenGeneratorInterface $token)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
        $this->token = $token;
    }

    /**
     * @Route("/signup", name="user.signup")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param GuardAuthenticator $authentificatorHandler
     * @param LoginFormAuthentificator $authentificator
     * @param ProfilesRepository $profilsRepository
     */
    public function signup(Request $request, UserPasswordEncoderInterface $encoder, 
                GuardAuthenticatorHandler $authentificatorHandler, LoginFormAuthenficator $authenticator, ProfilesRepository $profilsRepository): Response
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           
            $data = $form->getData();
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $encoder->encodePassword($data, $plainPassword);
            
            $data->setPassword($hashedPassword);
            $data->setTokenAccount($this->token->generateToken());
            $data->setAccountConfirmation(false);
        
            $this->manager->persist($data);

            $dataProfile = $form->get('profiles')->getData();
            $profile = $profilsRepository->findOneBy(['code' => $dataProfile]);

            $profiles = $profile->getProfiles();
            
            $individual = new Individual();
            $individual->setUser($data);
            foreach ($profiles as $profile) {
                $individual->addProfile($profile);
            } 
            $this->manager->persist($individual); 

            $this->manager->flush();

            $userMail = $data->getEmail();
            $email = (new TemplatedEmail())
                    ->from('mon-dossier-immo@support.com')
                    ->to($userMail)
                    ->replyTo('mon-dossier-immo@support.com')
                    ->subject('E-mail de comfirmation de compte')
                    ->context([
                         'data' => $data
                        ])
                    ->htmlTemplate('mail_template/signup/index.html.twig')
                    ;

            $this->mailer->send($email);


            $this->addFlash('success', 'Votre compte à bien été créer ! Un e-mail de confirmation vous à été envoyé.');
            $authentificatorHandler->authenticateUserAndHandleSuccess($data, $request, $authenticator,'main');
            return $this->redirectToRoute('user.account_confirmation', ['title' => 'Votre Compte à bien été créer !']);
        }
        
        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account-confirmation/{id}/{token}", name="user.account_confirmation.check")
     * @param User $user
     * @param IndividualDataService $dataService
     */

    public function accountConfirmationCheck($token, User $user, IndividualDataService $dataService)
    {
        if($user->getTokenAccount() === null || $token !== $user->getTokenAccount()){
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à être sur cette page !');
            return $this->redirectToRoute('home.index');
        }
       

        $individual = $user->getIndividual();
        $dataService->CreateIndividualData($individual);

        $user->setIndividual($individual);
        $user->setTokenAccount(NULL);
        $user->setAccountConfirmation(1);
        $this->manager->persist($user);

        $this->manager->flush();


        $this->addFlash('success', 'Votre compte à bien été activé, veuillez vous reconnectez.');
       
        return $this->redirectToRoute('security.login');
    }

    /**
     * @Route ("/account-confirmation/{title}", name="user.account_confirmation")
     * @param string $tilte
     */
    public function accountConfirmation(string $title)
    {

        return $this->render('user/accountConfirmation/index.html.twig', [
            'title' => $title
        ]);
    }

    /**
     * @Route("/password-recovery", name="user.password-recovery")
     * @param Request $request
     */
    public function PasswordRecovery(Request $request)
    {
        $form = $this->createForm(PasswordRecoveryType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $user = $this->userRepository->loadUserByEmail($form->getData()['email']);


            if(!$user){
                $this->addFlash('error', 'L\'e-mail précisé ne correspond à aucun compte');
                return $this->redirectToRoute('user.password-recovery');
            }

            $user->setPasswordToken($this->token->generateToken());
            $user->setPasswordRequestedAt(new \DateTime());
            $this->manager->persist($user);
            $this->manager->flush();


            $email = (new TemplatedEmail())
               ->from('mon-dossier-immo@support.com')
               ->to($data['email'])
                ->replyTo('mon-dossier-immo@support.com')
                ->subject('E-mail de récupération de mot de passe')
                ->context([
                    'user' => $user
                ])
                ->htmlTemplate('mail_template/ressetting_mp/index.html.twig');

            $this->mailer->send($email);

            $this->addFlash('success', 'Votre e-mail de récupération de mot de passe à bien été envoyé à l\'adresse précisé');
            return $this->redirectToRoute('security.login');
        }

        return $this->render('user/password_recovery/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function isRequestInTime(\DateTime $passwordRequestedAt = null)
    {
        if ($passwordRequestedAt === null)
        {
            return false;
        }

        $now = new \DateTime();
        $interval = $now->getTimestamp() - $passwordRequestedAt->getTimestamp();

        $daySeconds = 60 * 3600;
        $response = $interval > $daySeconds ? false : $reponse = true;
        return $response;
    }

    /**
     * @Route("/resetting/{id}/{token}", name="user.resetting")
     * @param User $user
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     */

    public function resetting($token, User $user, Request $request, UserPasswordEncoderInterface $encoder)
    {
        if($user->getPasswordToken() === null || $token !== $user->getPasswordToken() || !$this->isRequestInTime($user->getPasswordRequestedAt())){
            $this->addFlash('error', 'Vous n\'êtiez pas autorisé à être sur cette page !');
            return $this->redirectToRoute('home.index');
        }

        $form = $this->createForm(PasswordResettingType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $password = $encoder->encodePassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($password);

            $user->setPasswordToken(null);
            $user->setPasswordRequestedAt(null);

            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash('success', 'Votre mot de passe à bien été modifier !');
            return $this->redirectToRoute('security.login');
        }

        return $this->render('user/password_resetting/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
  
    /**
     * @Route("/user/dashboard/{id}/edit", name="user.edit")
     * @param User $user
     * @param Request $request
     */

    public function edit(User $user, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $this->manager->persist($data);
            $this->manager->flush();

            $id = $data->getId();
            $this->addFlash('success', 'Vos information on bien été modifier !');
            return $this->redirectToRoute('user.edit', ['id' => $id]);
        }

        return $this->render('user/Dashboard/update.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("user/dashboard/{id}/edit-password", name="user.edit-password")
     * @param User $user
     * @param Request $request
     * @param UserEncoderInterface $encoder
     */
    
     public function editPassword(User $user, Request $request, UserPasswordEncoderInterface $encoder)
     {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form =  $this->createForm(EditUserPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
               
            $password = $encoder->encodePassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($password);

            $this->manager->persist($user);
            $this->manager->flush();

            $session = new Session();
            $session->invalidate();
            
            $this->addFlash('success', 'Votre mot de passe à bien été modifier !');
            return $this->redirectToRoute('security.login');
            
        }
        $passwordUser = $user->getPassword();
        
        return $this->render('user/Dashboard/edit-password.html.twig', [
                'form' => $form->createView(),
                'password' => $passwordUser
        ]);
     }

     /**
     * @Route("user/dashboard/{id}/remove", name="user.remove")
     * @param User $user
     */

     public function remove(User $user)
     {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $this->manager->remove($user);
        $this->manager->flush();

        $session = new Session();
        $session->invalidate();
        
        return $this->redirectToRoute('security.logout');

     }

     /**
      * @Route("user/mes-informations-locataire/{id}", name="user.information_tenant")
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

            $individual = $user->getIndividual();
            $individualDataService->insertIndividualData($individual, $form, 'tenant', 'identity');

            $id = $user->getId();
            $this->addFlash('success', 'Vos données ont bien été modifié');
            return $this->redirectToRoute('user.information_tenant', ['id' => $id]);

          }

          $formDoc = $this->createForm(DocumentType::class, null, ['data_label' => 'label', 'action' => $this->generateUrl('user.tenant_upload_doc', ['id' => $user->getId()]), 'method' => 'POST']);
          $formInvitation = $this->createForm(InvitationType::class, null, ['action' => $this->generateUrl('user.tenant_invitation', ['id' => $user->getId()]), 'method' => 'POST']);
          


          return $this->render('user/Dashboard/information/identity/index.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
            'formDoc' => $formDoc->createView(),
            'formInvitation' => $formInvitation->createView(),
          ]);
      }


    /**
    * @Route("user/mes-informations-locataire/{id}/create-invitation", name="user.tenant_invitation", methods={"POST"})
    * @param Request $request
    */
    public function createInvitation(Request $request)
    {
        $req = $request->get('invitation');
        $email = $req['email'];

        $ref = $req['ref'];

        $individual = $this->getUser()->getIndividual();

        $invitation = new Invitation();
        $invitation->setEmail($email);
        $invitation->setIndividual($individual);

        $this->manager->persist($invitation);
        $this->manager->flush();

        $email = (new TemplatedEmail())
        ->from('mon-dossier-immo@support.com')
        ->to($email)
        ->replyTo('mon-dossier-immo@support.com')
        ->subject('Dossier de location pour le bien '.$ref)
        ->context([
             'ref' => $ref,
             'invitation' => $invitation->getId(),
            ])
        ->htmlTemplate('mail_template/Dossier-location/index.html.twig')
        ;

        $this->mailer->send($email);

        $this->addFlash('success', 'Votre invitation de dossier à bien été envoyé');
        return $this->redirectToRoute('user.information_tenant', ['id' => $this->getUser()->getId()]);
    }

     /**
      * @Route("user/mes-informations-vendeur/{id}", name="user.information_seller")
      * @param Request $request
      * @param IndividualDataService $individualDataService
      * @param IndividualDataRepository $individualDataRepository
      * @param User $user
      */
      public function EditInformationsSeller(Request $request, User $user, IndividualDataService $individualDataService, IndividualDataRepository $individualDataRepository)
      {
          $individual = $user->getIndividual();

          $datas = $individualDataRepository->getDataByIndividualAndProfile($individual, 'seller');

          $form = $this->createForm(IdentityType::class, null, ['data_profile' => 'seller' ,'data_category' => 'identity']);
          $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()){

            $individual = $user->getIndividual();
            $individualDataService->insertIndividualData($individual, $form, 'seller', 'identity');

            $id = $user->getId();
            $this->addFlash('success', 'Vos données ont bien été modifié');
            return $this->redirectToRoute('user.information_seller', ['id' => $id]);

          }

          $formDoc = $this->createForm(DocumentType::class, null, ['data_label' => 'label', 'action' => $this->generateUrl('user.seller_upload_doc', ['id' => $user->getId()]), 'method' => 'POST']);

          return $this->render('user/Dashboard/information/identity/index.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
            'formDoc' => $formDoc->createView(),
          ]);
      }

      /**
       * @Route("user/mes-informations-tenant/{id}/upload", name="user.tenant_upload_doc", methods={"POST"})
       * @param Request $request
       * @param UploadFilesHelper $uploadFilesHelper
       * @param User $user
       * @param IndividualDataCategoryRepository $categroyRepository
       * @param ProfilesRepository $profileRepository
       * @param ValidatorInterface $validator
       */
      public function tenantUplodadDocument(Request $request, User $user, UploadFilesHelper $uploadFilesHelper, IndividualDataCategoryRepository $categoryRepository, ValidatorInterface $validator, ProfilesRepository $profileRepository)
      {

        $individual = $user->getIndividual();

        // Récupération du document
        $req = $request->files->get('document');
        $file = $req['data'];

        // Récupération du titre du document
        $req = $request->get('document');
        $label = $req['label'];

        $category = $categoryRepository->findOneBy(['code' => 'identity']);
        $profile = $profileRepository->findOneBy(['code' => 'tenant']);

        $violations = $validator->validate($file, [new File([
            'maxSize' => '10000k', 
            'maxSizeMessage' => 'Le fichier est trop volumineux. Maximun autorisé : 1ko',
            'mimeTypes' => [
                'image/*',
                'application/pdf',
                'application/msword',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain'
            ],
            'mimeTypesMessage' => 'Type de fichier invalide']),
            New NotBlank(['message' => 'Merci de séléctionner un fichier.'])
        ]);

        $id = $user->getId();

        if($violations->count() > 0){
            $violations = $violations[0];
            $this->addFlash('error', $violations->getMessage());
            return $this->redirectToRoute('user.information_seller', ['id' => $id]);
        }
        
        $uploadFilesHelper->uploadFilePrivate($file, $label, $individual, $category, $profile);

        $this->addFlash('success', 'Votre documents à bien été téléchargé ! Vous pouvez le retouver dans votre rubrique "Mes documents".');
        return $this->redirectToRoute('user.document', ['id' => $id]);

      }

            /**
       * @Route("user/mes-informations-seller/{id}/upload", name="user.seller_upload_doc", methods={"POST"})
       * @param Request $request
       * @param UploadFilesHelper $uploadFilesHelper
       * @param User $user
       * @param IndividualDataCategoryRepository $categroyRepository
       * @param ProfilesRepository $profileRepository
       * @param ValidatorInterface $validator
       */
      public function sellerUplodadDocument(Request $request, User $user, UploadFilesHelper $uploadFilesHelper, IndividualDataCategoryRepository $categoryRepository, ValidatorInterface $validator, ProfilesRepository $profileRepository)
      {

        $individual = $user->getIndividual();

        // Récupération du document
        $req = $request->files->get('document');
        $file = $req['data'];

        // Récupération du titre du document
        $req = $request->get('document');
        $label = $req['label'];

        $category = $categoryRepository->findOneBy(['code' => 'identity']);
        $profile = $profileRepository->findOneBy(['code' => 'seller']);

        $violations = $validator->validate($file, [new File([
            'maxSize' => '10000k', 
            'maxSizeMessage' => 'Le fichier est trop volumineux. Maximun autorisé : 1ko',
            'mimeTypes' => [
                'image/*',
                'application/pdf',
                'application/msword',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain'
            ],
            'mimeTypesMessage' => 'Type de fichier invalide']),
            New NotBlank(['message' => 'Merci de séléctionner un fichier.'])
        ]);

        $id = $user->getId();

        if($violations->count() > 0){
            $violations = $violations[0];
            $this->addFlash('error', $violations->getMessage());
            return $this->redirectToRoute('user.information_seller', ['id' => $id]);
        }
        
        $uploadFilesHelper->uploadFilePrivate($file, $label, $individual, $category, $profile);

        $this->addFlash('success', 'Votre documents à bien été téléchargé ! Vous pouvez le retouver dans votre rubrique "Mes documents".');
        return $this->redirectToRoute('user.document', ['id' => $id]);

      }

      /**
       * @Route("/user/mes-documents/{id}", name="user.document")
       * @param User $user
       * @param DocumentRepository $documentRepository
       * @param IndividualDataCategoryRepository $individualDataCategory
       */
      public function EditDocument(User $user, DocumentRepository $documentRepository, IndividualDataCategoryRepository $individualDataCategoryRepository)
      {
            $individual = $user->getIndividual();
            
            $documents = [];

            $identity = $individualDataCategoryRepository->findOneBy(["code" => 'identity']);
            $domiciliation = $individualDataCategoryRepository->findOneBy(["code" => 'domiciliation']);

            $domiciliations = $documentRepository->findBy(["individual" => $individual, "category" => $domiciliation]);
            foreach ($domiciliations as $domiciliation){
                array_push($documents, $domiciliation);
            }
            $identitys = $documentRepository->findBy(["individual" => $individual, "category" => $identity]);
            foreach ($identitys as $identity){
                array_push($documents, $identity);
            }
            
            return $this->render('user/Dashboard/information/document/index.html.twig', [
                'documents' => $documents,
            ]);
      }

      /**
       * @Route("user/dossier-locataire/{invitation}/check", name="user.directory_tenant_check_email", methods={"GET"})
       * @param int $invitation
       * @param SessionInterface $session
       * @param InvitationRepository $invitationRepository
       */
      public function checkEmailDirectoryTenant($invitation, SessionInterface $session, InvitationRepository $invitationRepository)
      {
            $invit = $invitationRepository->findOneBy(['id' => $invitation]);

            if(!empty($session->get($invit->getIndividual()->getId()))){
                $session->remove($invit->getIndividual()->getId());
            }
            
            $codeBrut = mt_rand(1000, 9999);
            $session->get('ValidCode', []);
            $session->set('ValidCode',[number_format($codeBrut, 0,'', '')]);
            
            $email = $invit->getEmail();

            $mail = (new TemplatedEmail())
            ->from('mon-dossier-immo@support.com')
            ->to($email)
            ->replyTo('mon-dossier-immo@support.com')
            ->subject('Code de sécuirté')
            ->context([
                    'code' => $codeBrut,
                ])
            ->htmlTemplate('mail_template/Dossier-location/check/index.html.twig')
            ;
            $this->mailer->send($mail);

            $form = $this->createForm(CheckDirectoryType::class, null, ['action' => $this->generateUrl('user.directory_tenant_check_code', ['invitation' => $invitation])]);

            return $this->render('user/dossier-locataire/check/index.html.twig', [
                'form' => $form->createView(),
            ]);
      }

      /**
       * @Route("user/dossier-locataire/{invitation}/check", name="user.directory_tenant_check_code", methods={"POST"})
       * @param SessionInterface $session
       * @param Request $request
       * @param InvitationRepository $invitationRepository
       */
      public function checkCodeDirectoryTenant($invitation, Request $request, SessionInterface $session, InvitationRepository $invitationRepository)
      {
            $invit = $invitationRepository->findOneBy(['id' => $invitation]);

            $data = $request->get('check_directory');
        
            $code = $session->get('ValidCode');
            $number = number_format($data['number'], 0, '', '');

            if($number == $code[0]){
                
                $session->remove('ValidCode');

                $individual = $invit->getIndividual();

                $session->get($individual->getId(), []);
            
                $token = $this->token->generateToken();
                
                $session->set($individual->getId(), [$token]);
                return $this->redirectToRoute('user.directory_tenant', ['id' => $individual->getId(), 'token' => $token]);
            }else{
                $this->addFlash('error', 'mauvais code');
                return $this->redirectToRoute('user.directory_tenant_check_email', ['invitation' => $invitation]);
            }
      }

      /**
       * @Route("user/dossier-locataire/{id}/{token}", name="user.directory_tenant")
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
       * @Route("user/mes-documents/{id}/display/{token}", name="user.document_display")
       * @param string $token
       * @param Document $document
       * @param UploadFilesHelper $upload
       * @param SessionInterface $session
       * @param Security $security
       */
      public function DisplayDocument($token, Document $document, UploadFilesHelper $upload, SessionInterface $session, Security $security)
      {
         if(!empty($session->get($document->getIndividual()->getId()) || $document->getindividual()->getUser() == $security->getUser())){

             $directoryAccess = $session->get($document->getIndividual()->getId());

            if($document->getindividual()->getUser() == $security->getUser() || $directoryAccess[0] == $token){
                $response = new StreamedResponse(function() use ($document, $upload){
                    $outputStream = fopen('php://output', 'wb');
                    $fileStream = $upload->readStream($document->getFilePath(), false);
                    stream_copy_to_stream($fileStream, $outputStream);
                });
        
                $response->headers->set('Content-Type', $document->getMimeType());
        
                return $response;
            }
         }
            $this->addFlash('error', 'vous n\'êtes pas autorisé à consulter se document !');
            return $this->redirectToRoute('home.index');
     
      }

       /**
       * @Route("user/mes-documents/{id}/download/{token}", name="user.document_download")
       * @param Document $document
       * @param UploadFilesHelper $upload
       * @param SessionInterface $session
       * @param Security $security
       */
      public function DownloadDocument($token, Document $document, UploadFilesHelper $upload, SessionInterface $session, Security $security)
      {
        if(!empty($session->get($document->getIndividual()->getId()) || $document->getindividual()->getUser() == $security->getUser())){
            $directoryAccess = $session->get($document->getIndividual()->getId());

            if($document->getindividual()->getUser() == $security->getUser() || $directoryAccess[0] == $token){
                $response = new StreamedResponse(function() use ($document, $upload){
                    $outputStream = fopen('php://output', 'wb');
                    $fileStream = $upload->readStream($document->getFilePath(), false);
                    stream_copy_to_stream($fileStream, $outputStream);
                });
        
                $disposition = HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    $document->getData()
                );
        
                $response->headers->set('Content-Disposition', $disposition);
        
                return $response;
            }
        }

            $this->addFlash('error', 'vous n\'êtes pas autorisé à consulter se document !');
            return $this->redirectToRoute('home.index');
      }


      /**
       * @Route("user/mes-documents/{id}/delete", name="user.document_delete")
       * @param Document $document
       * @param UploadFilesHelper $upload
       */
      public function DeleteDocument(Document $document, UploadFilesHelper $upload)
      {
            $this->manager->remove($document);
            $upload->deleteFile($document->getFilePath(), false);
            $this->manager->flush();

            $this->addFlash('success', 'Votre document à bien été supprimé.');
            return $this->redirectToRoute('user.document', ['id' => $this->getUser()->getId()]);
      }

    /**
     * @Route("user/mes-garants/{id}", name="user.garant", methods={"GET"})
     * @param IndividualRepository $individualRepository
     * @param IndividualDataRepository $individualDataRepository
     */
    public function EditGuarant($id, IndividualRepository $individualRepository, IndividualDataRepository $individualDataRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $formGarant = $this->createForm(CreateGarantType::class, null, ['action' => $this->generateUrl('user.tenant_garant', ['id' => $this->getUser()->getId()]), 'method' => 'POST']);
        $InfoGarant = [];

        $individual = $individualRepository->findOneByIdUser($id);
        $garants = $individual->getIndividuals();
        foreach ($garants as $garant){
            $lastname = $individualDataRepository->getDataByCode($garant, 'lastname');
            $firstname = $individualDataRepository->getDataByCode($garant, 'firstname');
            $birthDate = $individualDataRepository->getDataByCode($garant, 'birth_date');
            $email = $garant->getUser()->getEmail();

            $Info = [
                'id' => $garant->getId(), 
                'firstname' => $firstname->getData(), 
                'lastname' => $lastname->getData(), 
                'birth_date' => $birthDate->getData(),
                'email' => $email
            ];

            array_push($InfoGarant, $Info);
        }

        // dd($InfoGarant);
        return $this->render('user/Dashboard/information/Garant/index.html.twig', [
            'infoGarants' => $InfoGarant,
            'formGarant' => $formGarant->createView(),
        ]);
    }

    /**
    * @Route("user/mes-garants/{id}/delete", name="user.garant_delete", methods={"GET"})
    * @param IndividualRepository $individualRepository
    */
    public function deleteGarant($id, IndividualRepository $individualRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $individual = $individualRepository->findOneByUser($this->getUser());
        $garant = $individualRepository->findOneBy(['id' => $id]);
        
        $individual->removeIndividual($garant);
        $this->manager->persist($individual);
        $this->manager->flush();

        $this->addFlash('success', 'Votre garant à bien été supprimée');
        return $this->redirectToRoute('user.garant', ['id' => $this->getUser()->getId()]);
        
    }

    /**
    * @Route("user/mes-informations-locataire/{id}/create-garant", name="user.tenant_garant", methods={"POST"})
    * @param Request $request
    * @param int $id
    */
    public function createGarant($id, Request $request)
    {
        $data = $request->get('create_garant');

        $email = (new TemplatedEmail())
        ->from('mon-dossier-immo@support.com')
        ->to($data['email'])
        ->replyTo('mon-dossier-immo@support.com')
        ->subject('Vous avez reçu une demande de garant')
        ->context([
             'user' => $this->getUser()
            ])
        ->htmlTemplate('mail_template/create-garant/index.html.twig')
        ;

        $this->mailer->send($email);

        $this->addFlash('success', 'Votre demande de garant à bien été envoyé à l\'email précisé !');
        return $this->redirectToRoute('user.garant', ['id' => $id]);
            
    }

    /**
    * @Route("user/mes-informations-locataire/{id}/active-garant", name="user.tenant_garant-summarize", methods={"GET"})
    * @param Request $request
    * @param int $id
    * @param SessionInterface $session
    * @param UriSigner $signer
    */
    public function SummarizeGarant($id, SessionInterface $session, Request $request, UriSigner $signer)
    {
        $signer->checkRequest($request);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $session->remove($id);
        $session->get($id, []);
        $token = $this->token->generateToken();  
        $session->set($id, $token);  
        // dd($session->get($id));    
        
        return $this->render('user/Dashboard/information/Garant/sumarize.html.twig', [
            'id' => $id,
            'token' => $token
        ]);  
    }

    /**
    * @Route("user/mes-informations-locataire/{id}/active-garant/{token}", name="user.tenant_garant-activate")
    * @param string $token
    * @param int $id
    * @param SessionInterface $session
    * @param Security $security
    * @param IndividualRepository $individualRepository
    */
    public function activeGarant($id, $token, SessionInterface $session, Security $security, IndividualRepository $individualRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // dd($session->get($id));
        if($session->get($id) !== $token){
            $this->addFlash('error', 'une erreur c\'est produite.');
            return $this->redirectToRoute('home.index');
        }

        $user = $security->getUser();
        $garant = $individualRepository->findOneByUser($user);
        
        $user = $this->userRepository->findOneBy(['id' => $id]);
        $individual = $user->getIndividual();

        $verif = [];
        foreach ($individual->getIndividuals() as $individu){
            array_push($verif, $individu->getId());
        }

        if(in_array($garant->getId(), $verif)){
            $this->addFlash('error', 'Vous êtes déjà garants de cette personne.');
            return $this->redirectToRoute('home.index');
        }

        $individual->addIndividual($garant);
        $this->manager->persist($individual);
        $this->manager->flush();

        $this->addFlash('success', 'Vous êtes maintenant garant !');
        return $this->redirectToRoute('home.index');
  
    }

    /**
     * @Route("user/mes-revenues/{id}", name="user.income", methods={"GET"})
     * @param IncomeTypeRepository $IncomeTypeRepository
     * @param IncomeYearRepository $incomeRepository
     * @param IncomeRepository $incomeRepository
     * @param DocumentRepository $documentRepository
     */
    public function EditIcomes(IncomeTypeRepository $IncomeTypeRepository, IncomeYearRepository $incomeYearRepository, IncomeRepository $incomeRepository, DocumentRepository $documentRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $typeCode = [];
        $IncomeType = $IncomeTypeRepository->findAll();
        foreach ($IncomeType as $type){
            $typeCode[$type->getLabel()] = $type->getCode();
        }

        $form = $this->createForm(IncomeType::class, null, ['data_type' => $typeCode]);
        $formDocYear = $this->createForm(DocumentType::class, null, ['data_label' => 'label', 'method' => 'POST']);
        $formDocIncome = $this->createForm(DocumentType::class, null, ['method' => 'POST']);  

        $income = [];

        $incomeYears = $incomeYearRepository->findBy(['individual' => $this->getUser()->getIndividual()]);

        foreach($incomeYears as $incomeYear){
            $documents = $documentRepository->findByYearAndIndividual($incomeYear, $this->getUser()->getIndividual());

            $income[$incomeYear->getCode()]['document'] = $documents;
        }

        foreach($incomeYears as $incomeYear){
            $incomes = $incomeRepository->findBy(['incomeYear' => $incomeYear]);
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
        
        // dd($income);

        return $this->render('user/Dashboard/information/income/index.html.twig', [
            'form' => $form->createView(),
            'incomes' => $income,
            'formDocYear' => $formDocYear->createView(),
            'formDocIncome' => $formDocIncome->createView(),
        ]);
    }

    /**
     * @Route("user/mes-revenues/{id}", name="user.income_create_income", methods={"POST"})
     * @param Request $request
     * @param IncomeTypeRepository $incomeTypeRepository
     * @param IncomeYearRepository $IncomeYearRepository
     */
    public function AddIcomes($id, Request $request, IncomeTypeRepository $incomeTypeRepository, IncomeYearRepository $incomeYearRepository)
    {
       $this->denyAccessUnlessGranted('ROLE_USER');

       $individual = $this->getUser()->getIndividual();
       $data = $request->get('income');
       $year = $incomeYearRepository->findOneBy(['code' => $data['year']]);
       $type = $incomeTypeRepository->findOneBy(['code' => $data['type']]);
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

       $this->addFlash('success', 'Votre revenue à bien été publié.');
       return $this->redirectToRoute('user.income', ['id' => $id]);
    }

    /**
     * @Route("user/mes-revenues/{id}/{code}/upload", name="user.income_upload_year", methods={"POST"})
     * @param string $code
     * @param Request $request
     * @param IndividualDataCategoryRepository $categoryRepository
     * @param  ProfilesRepository $profileRepository
     * @param UploadFilesHelper $uploadFilesHelper
     * @param ValidatorInterface $validator
     * @param IncomeYearRepository $incomeYearRepository
     */
    public function uploadDocIncomeYear($code, Request $request, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository, UploadFilesHelper $uploadFilesHelper, ValidatorInterface $validator, IncomeYearRepository $incomeYearRepository)
    {
        // dd($request->get('document'));
        $individual = $this->getUser()->getIndividual();

        // Récupération du document
        $file = $request->files->get('document');

        // Récupération du titre du document
        $label = $request->get('document');

        $category = $categoryRepository->findOneBy(['code' => 'incomes']);
        $profile = $profileRepository->findOneBy(['code' => 'tenant']);

        $violations = $validator->validate($file['data'], [new File([
            'maxSize' => '10000k', 
            'maxSizeMessage' => 'Le fichier est trop volumineux. Maximun autorisé : 1ko',
            'mimeTypes' => [
                'image/*',
                'application/pdf',
                'application/msword',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain'
            ],
            'mimeTypesMessage' => 'Type de fichier invalide']),
            New NotBlank(['message' => 'Merci de séléctionner un fichier.'])
        ]);


        if($violations->count() > 0){
            $violations = $violations[0];
            $this->addFlash('error', $violations->getMessage());
            return $this->redirectToRoute('user.income', ['id' => $this->getUser()->getId()]);
        }
        
        $year = $incomeYearRepository->findOneByCodeAndIndividual( $code, $individual);

        $uploadFilesHelper->uploadFilePrivate($file['data'], $label['label'], $individual, $category, $profile, null, $year);

        $this->addFlash('success', 'Votre revenue pour l\'année '.$code.' à bien été téléchargé.');
        return $this->redirectToRoute('user.income', ['id' => $this->getUser()->getId()]);
    }

        /**
     * @Route("user/mes-revenues/{id}/upload/{income}", name="user.income_upload", methods={"POST"})
     * @param int $income
     * @param Request $request
     * @param IndividualDataCategoryRepository $categoryRepository
     * @param  ProfilesRepository $profileRepository
     * @param UploadFilesHelper $uploadFilesHelper
     * @param ValidatorInterface $validator
     * @param IncomeRepository $incomeRepository
     */
    public function uploadDocIncome($income, Request $request, IndividualDataCategoryRepository $categoryRepository, ProfilesRepository $profileRepository, UploadFilesHelper $uploadFilesHelper, ValidatorInterface $validator, IncomeRepository $incomeRepository)
    {
        // dd($request->files->get('document'));
        $individual = $this->getUser()->getIndividual();

        // Récupération du document
        $file = $request->files->get('document');

        $category = $categoryRepository->findOneBy(['code' => 'incomes']);
        $profile = $profileRepository->findOneBy(['code' => 'tenant']);

        $violations = $validator->validate($file['data'], [new File([
            'maxSize' => '10000k', 
            'maxSizeMessage' => 'Le fichier est trop volumineux. Maximun autorisé : 1ko',
            'mimeTypes' => [
                'image/*',
                'application/pdf',
                'application/msword',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain'
            ],
            'mimeTypesMessage' => 'Type de fichier invalide']),
            New NotBlank(['message' => 'Merci de séléctionner un fichier.'])
        ]);


        if($violations->count() > 0){
            $violations = $violations[0];
            $this->addFlash('error', $violations->getMessage());
            return $this->redirectToRoute('user.income', ['id' => $this->getUser()->getId()]);
        }
        
        $incomeData = $incomeRepository->findOneBy(['id' => $income]);

        $uploadFilesHelper->uploadFilePrivate($file['data'], $incomeData->getLabel(), $individual, $category, $profile, $incomeData, null);

        $this->addFlash('success', 'Votre document associé au revenue '.$incomeData->getLabel().' à bien été téléchargé.');
        return $this->redirectToRoute('user.income', ['id' => $this->getUser()->getId()]);
    }

}