<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Entity\Individual;
use App\Form\DocumentType;
use App\Form\EditUserType;
use App\Form\IdentityType;
use App\Entity\IndividualData;
use App\Entity\ProfilModelData;
use App\Form\EditUserPasswordType;
use App\Form\PasswordRecoveryType;
use App\Repository\UserRepository;
use App\Form\PasswordResettingType;
use App\Repository\ProfilesRepository;
use App\Services\IndividualDataService;
use App\Security\LoginFormAuthenficator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\IndividualDataRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Repository\ProfilModelDataRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;


class UserController extends AbstractController
{

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
     * @Route("/user/dashboard/edit/{id<\d+>}", name="user.edit")
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
     * @Route("user/dashboard/edit-password/{id<\d+>}", name="user.edit-password")
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
     * @Route("user/dashboard/remove/{id<\d+>}", name="user.remove")
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
          $individual = $user->getIndividual();

          $datas = $individualDataRepository->getDataByIndividualAndProfile($individual, 'tenant');

          $form = $this->createForm(IdentityType::class, null, ['data_profile' => 'tenant' ,'data_category' => 'identity']);
          $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()){

            $individual = $user->getIndividual();
            $individualDataService->insertIndividualData($individual, $form);

            $id = $user->getId();
            $this->addFlash('success', 'Vos données ont bien été modifié');
            return $this->redirectToRoute('user.information_tenant', ['id' => $id]);

          }

          $formDoc = $this->createForm(DocumentType::class, null, ['data_profile' => 'seller', 'data_category' => 'document', 'action' => $this->generateUrl('user.uploadDoc', ['id' => $user->getId()])]);

          return $this->render('user/Dashboard/information/identity/index.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
            'formDoc' => $formDoc->createView(),
          ]);
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
            $individualDataService->insertIndividualData($individual, $form);

            $id = $user->getId();
            $this->addFlash('success', 'Vos données ont bien été modifié');
            return $this->redirectToRoute('user.information_seller', ['id' => $id]);

          }

          $formDoc = $this->createForm(DocumentType::class, null, ['data_profile' => 'seller', 'data_category' => 'document', 'action' => $this->generateUrl('user.uploadDoc', ['id' => $user->getId()]), 'method' => 'POST']);

          return $this->render('user/Dashboard/information/identity/index.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
            'formDoc' => $formDoc->createView(),
          ]);
      }

      /**
       * @Route("user/mes-information-vendeur/upload/{id}", name="user.uploadDoc")
       */
      public function UplodadDocument()
      {
          dd($_POST);
      }

}
