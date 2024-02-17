<?php

namespace App\Twig\Components;

use App\Repository\PortfolioPersoRepository;
use App\Repository\PortfolioUnivRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PortfolioCard
{
    public int $id;

    public function __construct(
        protected PortfolioPersoRepository $portfolioPersoRepository,
        protected PortfolioUnivRepository $portfolioUnivRepository,
    )
    {
    }

    public function getPortfolio()
    {
        if ($this->portfolioUnivRepository->find($this->id) !== null) {
            $portfolio = $this->portfolioUnivRepository->find($this->id);
        } else {
            $portfolio = $this->portfolioPersoRepository->find($this->id);
        }

        return $portfolio;
    }
}
