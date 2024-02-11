<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardEtudiantController extends AbstractController
{
    #[Route('/dashboard/etudiant', name: 'app_dashboard_etudiant')]
    public function index(): Response
    {
        return $this->render('dashboard_etudiant/index.html.twig', [
        ]);
    }
}
