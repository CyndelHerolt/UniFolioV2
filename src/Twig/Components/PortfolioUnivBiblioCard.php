<?php

namespace App\Twig\Components;

use App\Repository\PortfolioUnivRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PortfolioUnivBiblioCard
{
    public int $id;

    public function __construct(
        protected PortfolioUnivRepository $portfolioUnivRepository,
    )
    {
    }

    public function getPortfolioUniv()
    {
        $portfolioUniv = $this->portfolioUnivRepository->find($this->id);

        return $portfolioUniv;
    }
}
