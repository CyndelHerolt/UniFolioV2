<?php

namespace App\Twig\Components;

use App\Repository\PortfolioUnivRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class AllPortfolios
{
    use DefaultActionTrait;

    public function __construct(
        private readonly Security $security,
        protected PortfolioUnivRepository $portfolioUnivRepository,
    )
    {

    }

    public function getAllPortfolios()
    {
        $portfolios = $this->portfolioUnivRepository->findBy(['etudiant' => $this->security->getUser()->getEtudiant()]);

        return $portfolios;
    }
}
