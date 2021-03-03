<?php

namespace App\Controller;

use App\Security\Access;
use App\Services\MailService;
use App\Form\CreateGarantType;
use App\Form\CheckDirectoryType;
use App\Services\GuarantorHelper;
use App\Repository\UserRepository;
use App\Services\IndividualDataService;
use App\Repository\IndividualRepository;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\IndividualDataRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\InvitationCategoryRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class GuarantorController extends AbstractController
{
    /**
     * @Route("mes-garants/{id}", name="user.garant", methods={"GET"})
     * @param int $id
     * @param IndividualRepository $individualRepository
     * @param GuarantorHelper $guarantorHelper
     * @param Access $access
     */
    public function EditGuarant($id, Access $access, IndividualRepository $individualRepository, GuarantorHelper $guarantorHelper)
    {
        if($access->accessDashboard($id) !== true){
            $this->addFlash('error', 'Access denied !');
            return $this->redirectToRoute('home.index');
          }
        $formGarant = $this->createForm(CreateGarantType::class, null, ['action' => $this->generateUrl('guarantor.create-invitation', ['id' => $this->getUser()->getId()]), 'method' => 'POST']);
        
        $individual = $individualRepository->findOneByIdUser($id);
        $garants = $individual->getIndividuals();
        $guarantorOf = $individualRepository->GarantIndividual($individual->getId());

        $guarantorOf = $guarantorHelper->GuarantorDisplay($guarantorOf);
        $DataGarant = $guarantorHelper->GuarantorDisplay($garants);
        
        return $this->render('user/Dashboard/information/Garant/index.html.twig', [
            'infoGarants' => $DataGarant,
            'formGarant' => $formGarant->createView(),
            'guarantorOf' => $guarantorOf,
        ]);
    }

    /**
    * @Route("mes-garants/{id}", name="guarantor.create-invitation", methods={"POST"})
    * @param int $id
    * @param Request $request
    * @param MailService $mailService
    * @param IndividualDataService $dataService
    * @param Access $access
    */
    public function createGarant($id, Access $access, Request $request, MailService $mailService, IndividualDataService $dataService)
    {
        if($access->accessDashboard($id) !== true){
            $this->addFlash('error', 'Access denied !');
            return $this->redirectToRoute('home.index');
          }

        $data = $request->get('create_garant');

        $invitation = $dataService->InvitationCreate($data['email'], $this->getUser()->getIndividual(), 'guarantor');

        $subject = 'Vous avez reçu une demande de garant';
        $template = 'mail_template/create-garant/index.html.twig';
        $mailService->PostMail($data['email'], $subject, $template, ['invitation' => $invitation->getId()]);

        $this->addFlash('success', 'Votre demande de garant à bien été envoyé à l\'email précisé !');
        return $this->redirectToRoute('user.garant', ['id' => $id]);
            
    }

    /**
    * @Route("mes-garants/{id}/delete", name="guarantor.delete", methods={"GET"})
    * @param IndividualRepository $individualRepository
    * @param EntityManagerInterface
    */
    public function deleteGarant($id, IndividualRepository $individualRepository, EntityManagerInterface $manager)
    {

        $individual = $individualRepository->findOneByUser($this->getUser());
        $garant = $individualRepository->findOneBy(['id' => $id]);
        
        $individual->removeIndividual($garant);
        $manager->persist($individual);
        $manager->flush();

        $this->addFlash('success', 'Votre garant à bien été supprimée');
        return $this->redirectToRoute('user.garant', ['id' => $this->getUser()->getId()]);
        
    }

    /**
     * @Route("mes-informations-garant/{invitation}/check", name="guarantor.invitation-check-email", methods={"GET"})
     * @param int $invitation
     * @param SessionInterface $session
     * @param InvitationRepository $invitationRepository
     * @param MailService $mail
     */
    public function checkEmailGuarantor($invitation, SessionInterface $session, InvitationRepository $invitationRepository, MailService $mail)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $invit = $invitationRepository->findOneBy(['id' => $invitation]);
        $form = $this->createForm(CheckDirectoryType::class, null, ['action' => $this->generateUrl('guarantor.invitation-check-code', ['invitation' => $invitation])]);

        if(!empty($session->get($invit->getIndividual()->getId()))){
            $session->remove($invit->getIndividual()->getId());
          }
              
        $codeBrut = mt_rand(1000, 9999);
        $session->get('ValidCodeGarant', []);
        $session->set('ValidCodeGarant',[number_format($codeBrut, 0,'', '')]);

        $subject = 'Code de sécuirté';
        $template = 'mail_template/Dossier-location/check/index.html.twig';
        $mail->PostMail($invit->getEmail(), $subject, $template, ['code' => $codeBrut]);

        return $this->render('user/Dashboard/information/Garant/checkEmail.html.twig', [
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("mes-informations-garant/{invitation}/check", name="guarantor.invitation-check-code", methods={"POST"})
     * @param int $invitation
     * @param Request $request
     * @param SessionInterface $session
     * @param TokenGeneratorInterface $tokenInterface
     */
    public function checkCodeGuarantor($invitation, Request $request, SessionInterface $session, TokenGeneratorInterface $tokenInterface)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $data = $request->get('check_directory');
        $code = $session->get('ValidCodeGarant');

        if(!$data['number']){
          $this->addFlash('error', 'Vous devez précisez un code !');
          return $this->redirectToRoute('guarantor.invitation-check-email', ['invitation' => $invitation]);
        }

        $number = number_format($data['number'], 0, '', '');
        if($number == $code[0]){
          
            $session->remove('ValidCodeGarant');
            $session->remove($invitation);
            $session->get($invitation, []);
            $token = $tokenInterface->generateToken();  
            $session->set($invitation, $token); 

            return $this->redirectToRoute('guarantor.create-invitation-summarize', ['invitation' => $invitation, 'token' => $token]);
        }else{
          $this->addFlash('error', 'mauvais code');
          return $this->redirectToRoute('guarantor.invitation-check-email', ['invitation' => $invitation]);
        }
    }

    /**
    * @Route("mes-informations-garant/{invitation}/{token}/active-garant", name="guarantor.create-invitation-summarize", methods={"GET"})
    * @param int $invitation
    * @param string $token
    * @param SessionInterface $session
    */
    public function SummarizeGarant($invitation, $token, SessionInterface $session)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
         
        if($session->get($invitation) !== $token){
            $this->addFlash('error', 'une erreur c\'est produite.');
            return $this->redirectToRoute('home.index');
        }
        
        return $this->render('user/Dashboard/information/Garant/sumarize.html.twig', [
            'invitation' => $invitation,
            'token' => $token
        ]);  
    }

    /**
    * @Route("mes-informations-garant/{invitation}/active-garant/{token}", name="guarantor.create-invitation-activate", methods={"GET"})
    * @param string $token
    * @param int $invitation
    * @param SessionInterface $session
    * @param Security $security
    * @param IndividualRepository $individualRepository
    * @param GuarantorHelper $guarantorHelper
    * @param UserRepository $userRepository
    */
    public function activeGarant($invitation, $token, SessionInterface $session, Security $security, IndividualRepository $individualRepository, 
        GuarantorHelper $guarantorHelper)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($session->get($invitation) !== $token){
            $this->addFlash('error', 'une erreur c\'est produite.');
            return $this->redirectToRoute('home.index');
        }

        $garant = $individualRepository->findOneByUser($security->getUser());
        $individual = $individualRepository->findOneByInvitation($invitation);

        $activate = $guarantorHelper->GuarantorActivate($garant, $individual);

        if($activate !== true){
            $this->addFlash('error', 'Vous êtes déjà garants de cette personne.');
            return $this->redirectToRoute('home.index');
        }

        $this->addFlash('success', 'Vous êtes maintenant garant !');
        return $this->redirectToRoute('home.index');
  
    }
}
