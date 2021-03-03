<?php

namespace App\Controller;

use App\Services\IndividualDataService;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\CreateProjectMinimaHelper;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home.index")
     */
    public function index(){

        return $this->render('home/index.html.twig', [
            
        ]);
    }
}
