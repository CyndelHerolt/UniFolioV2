<?php

namespace App\Controller\Etudiant;

use App\Entity\PortfolioUniv;
use App\Form\PortfolioUnivType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant/portfolio/univ')]
class PortfolioUnivController extends AbstractController
{
    #[Route('/', name: 'app_portfolio_univ')]
    public function index(): Response
    {
        return $this->render('portfolio_univ/index.html.twig', [
            'controller_name' => 'PortfolioUnivController',
        ]);
    }

    #[Route('/new', name: 'app_portfolio_univ_new')]
    public function create(): Response
    {
        $portfolio = new PortfolioUniv();

        $form = $this->createForm(PortfolioUnivType::class, $portfolio);

        return $this->render('portfolio_univ/form.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form->createView(),
        ]);
    }
}
