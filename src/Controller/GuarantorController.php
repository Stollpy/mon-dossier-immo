<?php

namespace App\Controller;

use App\Services\MailService;
use App\Form\CreateGarantType;
use App\Services\GuarantorHelper;
use App\Repository\UserRepository;
use App\Repository\IndividualRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\IndividualDataRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class GuarantorController extends AbstractController
{
    /**
     * @Route("mes-garants/{id}", name="user.garant", methods={"GET"})
     * @param IndividualRepository $individualRepository
     * @param GuarantorHelper $guarantorHelper
     */
    public function EditGuarant($id, IndividualRepository $individualRepository, GuarantorHelper $guarantorHelper)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $formGarant = $this->createForm(CreateGarantType::class, null, ['action' => $this->generateUrl('guarantor.create-invitation', ['id' => $this->getUser()->getId()]), 'method' => 'POST']);
        
        $individual = $individualRepository->findOneByIdUser($id);
        $garants = $individual->getIndividuals();

        $DataGarant = $guarantorHelper->GuarantorDisplay($garants);
        
        return $this->render('user/Dashboard/information/Garant/index.html.twig', [
            'infoGarants' => $DataGarant,
            'formGarant' => $formGarant->createView(),
        ]);
    }

    /**
    * @Route("mes-garants/{id}", name="guarantor.create-invitation", methods={"POST"})
    * @param Request $request
    * @param int $id
    * @param MailService $mailService
    */
    public function createGarant($id, Request $request, MailService $mailService)
    {
        $data = $request->get('create_garant');

        $subject = 'Vous avez reçu une demande de garant';
        $template = 'mail_template/create-garant/index.html.twig';

        $mailService->PostMail($data['email'], $subject, $template, ['user' => $this->getUser()]);

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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $individual = $individualRepository->findOneByUser($this->getUser());
        $garant = $individualRepository->findOneBy(['id' => $id]);
        
        $individual->removeIndividual($garant);
        $manager->persist($individual);
        $manager->flush();

        $this->addFlash('success', 'Votre garant à bien été supprimée');
        return $this->redirectToRoute('user.garant', ['id' => $this->getUser()->getId()]);
        
    }

    /**
    * @Route("mes-informations-garant/{id}/active-garant", name="guarantor.create-invitation-summarize", methods={"GET"})
    * @param int $id
    * @param SessionInterface $session
    * @param TokenGeneratorInterface $tokenInterface
    */
    public function SummarizeGarant($id, SessionInterface $session, TokenGeneratorInterface $tokenInterface)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $session->remove($id);
        $session->get($id, []);
        $token = $tokenInterface->generateToken();  
        $session->set($id, $token);  
  
        
        return $this->render('user/Dashboard/information/Garant/sumarize.html.twig', [
            'id' => $id,
            'token' => $token
        ]);  
    }

    /**
    * @Route("mes-informations-garant/{id}/active-garant/{token}", name="guarantor.create-invitation-activate")
    * @param string $token
    * @param int $id
    * @param SessionInterface $session
    * @param Security $security
    * @param IndividualRepository $individualRepository
    * @param GuarantorHelper $guarantorHelper
    * @param UserRepository $userRepository
    */
    public function activeGarant($id, $token, SessionInterface $session, Security $security, IndividualRepository $individualRepository, 
        UserRepository $userRepository, GuarantorHelper $guarantorHelper)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($session->get($id) !== $token){
            $this->addFlash('error', 'une erreur c\'est produite.');
            return $this->redirectToRoute('home.index');
        }

        $garant = $individualRepository->findOneByUser($security->getUser());
        
        $user = $userRepository->findOneBy(['id' => $id]);
        $individual = $user->getIndividual();

        $activate = $guarantorHelper->GuarantorActivate($garant, $individual);

        if($activate !== true){
            $this->addFlash('error', 'Vous êtes déjà garants de cette personne.');
            return $this->redirectToRoute('home.index');
        }

        $this->addFlash('success', 'Vous êtes maintenant garant !');
        return $this->redirectToRoute('home.index');
  
    }
}
