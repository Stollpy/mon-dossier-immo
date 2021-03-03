<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Security\Access;
use App\Form\EditUserType;
use App\Services\MailService;
use App\Form\EditUserPasswordType;
use App\Form\PasswordRecoveryType;
use App\Repository\UserRepository;
use App\Form\PasswordResettingType;
use App\Repository\ProfilesRepository;
use App\Services\IndividualDataService;
use App\Security\LoginFormAuthenficator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;


class UserController extends AbstractController
{
    /**
     * @Route("/signup", name="user.signup")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param GuardAuthenticator $authentificatorHandler
     * @param LoginFormAuthentificator $authentificator
     * @param ProfilesRepository $profilsRepository
     * @param TokenGeneratorInterface $tokenInterface
     * @param EntityManagerInterface $manager
     * @param MailService $mail
     * @param IndividualDataService $dataService
     */
    public function signup(Request $request, UserPasswordEncoderInterface $encoder, TokenGeneratorInterface $tokenInterface, EntityManagerInterface $manager,
         GuardAuthenticatorHandler $authentificatorHandler, LoginFormAuthenficator $authenticator, MailService $mail, IndividualDataService $dataService): Response
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           
            $data = $form->getData();
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $encoder->encodePassword($data, $plainPassword);
            
            $data->setPassword($hashedPassword);
            $data->setTokenAccount($tokenInterface->generateToken());
            $data->setAccountConfirmation(false);
            $manager->persist($data);

            $dataProfile = $form->get('profiles')->getData();
            $dataService->CreateIndividual($data, $dataProfile);
            $manager->flush();

            $userMail = $data->getEmail();
            $subject = 'E-mail de comfirmation de compte';
            $template = 'mail_template/signup/index.html.twig';
            $mail->PostMail($userMail, $subject, $template, ['data' => $data]);

            $this->addFlash('success', 'Votre compte à bien été créer ! Un e-mail de confirmation vous à été envoyé.');
            $authentificatorHandler->authenticateUserAndHandleSuccess($data, $request, $authenticator,'main');
            return $this->redirectToRoute('user.account_confirmation');
        }
        
        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account-confirmation/{id}/{token}", name="user.account_confirmation.check")
     * @param User $user
     * @param IndividualDataService $dataService
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     */

    public function accountConfirmationCheck($token, User $user, IndividualDataService $dataService, EntityManagerInterface $manager, SessionInterface $session)
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
        $manager->persist($user);
        $manager->flush();

        $session->clear();

        $this->addFlash('success', 'Votre compte à bien été activé, veuillez vous reconnectez.');
        return $this->redirectToRoute('security.login');
    }

    /**
     * @Route ("/account-confirmation", name="user.account_confirmation")
     */
    public function accountConfirmation()
    {
        return $this->render('user/accountConfirmation/index.html.twig', [
        ]);
    }

    /**
     * @Route("/password-recovery", name="user.password-recovery")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param TokenGeneratorInterface $tokenInterface
     * @param EntityManagerInterface $manager
     * @param MailService $mail
     */
    public function PasswordRecovery(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenInterface, EntityManagerInterface $manager, 
        MailService $mail)
    {
        $form = $this->createForm(PasswordRecoveryType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $user = $userRepository->loadUserByEmail($form->getData()['email']);
            if(!$user){
                $this->addFlash('error', 'L\'e-mail précisé ne correspond à aucun compte');
                return $this->redirectToRoute('user.password-recovery');
            }

            $user->setPasswordToken($tokenInterface->generateToken());
            $user->setPasswordRequestedAt(new \DateTime());
            $manager->persist($user);
            $manager->flush();

            $subject = 'E-mail de récupération de mot de passe';
            $template = 'mail_template/ressetting_mp/index.html.twig';
            $mail->PostMail($data['email'], $subject, $template, ['user' => $user]);

            $this->addFlash('success', 'Votre e-mail de récupération de mot de passe à bien été envoyé à l\'adresse précisé');
            return $this->redirectToRoute('security.login');
        }

        return $this->render('user/password_recovery/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function isRequestInTime(\DateTime $passwordRequestedAt = null)
    {
        if ($passwordRequestedAt === null){
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
     * @param EntityManagerInterface $manager
     */

    public function resetting($token, User $user, Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        if($user->getPasswordToken() === null || $token !== $user->getPasswordToken() || !$this->isRequestInTime($user->getPasswordRequestedAt()) ){
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
            $manager->persist($user);
            $manager->flush();

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
     * @param EntityManagerInterface $manager
     * @param Access $access
     */

    public function edit(Access $access, User $user, Request $request, EntityManagerInterface $manager)
    {
        if($access->accessDashboard($user->getId()) !== true){
            $this->addFlash('error', 'Access denied !');
            return $this->redirectToRoute('home.index');
          }

        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            
            $data = $form->getData();
            $manager->persist($data);
            $manager->flush();

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
     * @param EntityManagerInterface $manager
     * @param Access $access
     */
    
     public function editPassword(Access $access, User $user, Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
     {
        if($access->accessDashboard($user->getId()) !== true){
            $this->addFlash('error', 'Access denied !');
            return $this->redirectToRoute('home.index');
          }

        $form =  $this->createForm(EditUserPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
               
            $password = $encoder->encodePassword($user, $form->get('plainPassword')->getData());
            
            $user->setPassword($password);
            $manager->persist($user);
            $manager->flush();

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
     * @param EntityManagerInterface $manager
     * @param Access $access
     */
     public function remove(Access $access, User $user, EntityManagerInterface $manager)
     {
        if($access->accessDashboard($user->getId()) !== true){
            $this->addFlash('error', 'Access denied !');
            return $this->redirectToRoute('home.index');
          }

        $manager->remove($user);
        $manager->flush();

        $session = new Session();
        $session->invalidate();
        
        return $this->redirectToRoute('security.logout');
     }
}