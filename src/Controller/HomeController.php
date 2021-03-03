<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Individual;
use App\Services\IndividualDataService;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\CreateProjectMinimaHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home.index")
     */
    public function index(CreateProjectMinimaHelper $createProjectMinimaHelper, IndividualDataService $dataService, EntityManagerInterface $manager){
        // $user = new User();
        // $user->setEmail('tt@gmail.com');
        // $user->setPassword('user');
        // $user->setAccountConfirmation('true');
        // $manager->persist($user);
        // $individual = $dataService->createIndividual($user, 'individual');
        // dd($dataService->createIndividualData($individual));
        return $this->render('home/index.html.twig', [
            
        ]);
    }
}
