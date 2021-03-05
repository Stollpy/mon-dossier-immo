<?php

namespace App\Controller;

use App\Repository\AdsRepository;
use Symfony\Component\HttpFoundation\Response;
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
