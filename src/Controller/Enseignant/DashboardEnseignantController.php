<?php

namespace App\Controller\Enseignant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardEnseignantController extends AbstractController
{
    #[Route('/dashboard/enseignant', name: 'app_dashboard_enseignant')]
    public function index(): Response
    {
        return $this->render('dashboard_enseignant/index.html.twig', [
            'controller_name' => 'DashboardEnseignantController',
        ]);
    }
}
