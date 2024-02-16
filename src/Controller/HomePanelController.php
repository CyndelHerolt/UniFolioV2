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
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ETUDIANT')) {
                return $this->render('/home_panel/etudiant_panel.html.twig');
            } elseif ($this->isGranted('ROLE_ENSEIGNANT')) {
                return $this->render('/home_panel/enseignant_panel.html.twig', [
                    'controller_name' => 'HomePanelController',
                ]);
            }
        }
        return $this->redirectToRoute('app_login');
    }
}
