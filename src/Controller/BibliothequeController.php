<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant/bibliotheque')]
class BibliothequeController extends BaseController
{
    #[Route('/traces', name: 'app_biblio_traces')]
    public function index(Request $request): Response
    {
        return $this->render('bibliotheque/index.html.twig', [
        ]);
    }
}
