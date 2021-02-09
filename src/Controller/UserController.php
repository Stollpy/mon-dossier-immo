<?php

namespace App\Controller;




use App\Entity\User;
use App\Form\UserType;
use App\Form\PasswordRecoveryType;
use App\Repository\UserRepository;
use App\Form\PasswordResettingType;
use App\Security\LoginFormAuthenficator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     */
    public function signup(Request $request, UserPasswordEncoderInterface $encoder, 
                GuardAuthenticatorHandler $authentificatorHandler, LoginFormAuthenficator $authenticator): Response
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
            return $authentificatorHandler->authenticateUserAndHandleSuccess($data, $request, $authenticator,'main');
        }
        
        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account-confirmation/{id}/{token}", name="user.account_confirmation")
     */

    public function accountConfirmation($token, User $user)
    {
        if($user->getTokenAccount() === null || $token !== $user->getTokenAccount()){
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à être sur cette page !');
            return $this->redirectToRoute('home.index');
        }


        $user->setTokenAccount(NULL);
        $user->setAccountConfirmation(1);
        $this->manager->persist($user);
        $this->manager->flush();

        $this->addFlash('success', 'Votre compte à bien été activé, veuillez vous reconnectez.');
       
        return $this->redirectToRoute('security.login');
    }

    /**
     * @Route("/password-recovery", name="user.password-recovery")
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

            //$password = random_bytes( 10 );
           // $plainPassword = bin2hex($password);
            //$hashedPassword = $encoder->encodePassword($data, $plainPassword);


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
}
