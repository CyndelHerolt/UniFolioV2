<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomePanelController extends AbstractController
{
    #[Route('/home', name: 'app_home_panel')]
    public function index(): Response
    {
        return $this->render('home_panel/index.html.twig', [
            'controller_name' => 'HomePanelController',
        ]);
    }
}
