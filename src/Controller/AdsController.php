<?php

namespace App\Controller;

use App\Entity\Ads;
use App\Form\AdsType;
use App\Repository\AdsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AdsCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AdsController extends AbstractController
{
    /**
     * @Route("/annonce/{id}", name="ads.index")
     */
    public function index($id): Response
    {

        return $this->render('ads/index.html.twig', [
            'id' => $id,
        ]);
    }

    /**
    * @Route("/annonce/{id}/edit", name="ads.edit")
    * @param Ads $ads
    * @param AdsRepository $adsRepository
    * @param Request $request
    * @param AdsCategoryRepository $adsCategoryRepository
    * @param EntityManagerInterface $manager
    */
    public function edit($id, Ads $ads, Request $request, AdsCategoryRepository $adsCategoryRepository, AdsRepository $adsRepository, EntityManagerInterface $manager)
    {   
        $form = $this->createForm(AdsType::class, $ads);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $annonce = $adsRepository->findOneBy(['id' => $id]);
            $category = $adsCategoryRepository->findOneBy(['code' => $form->get('category')->getData()]);
            $annonce->setTitle($form->get('title')->getData());
            $annonce->setAdsCategory($category);
            $annonce->setContent($form->get('content')->getData());
            $annonce->setPrice($form->get('price')->getData());
            $manager->persist($annonce);
            $manager->flush();
            $this->addFlash('success', 'Votre annonce à bien été modifier');
            return $this->redirectToRoute('ads.edit', ['id' => $id]);
            
        }
        return $this->render('ads/edit.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
        ]);
    }
}
