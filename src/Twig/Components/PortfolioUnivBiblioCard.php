<?php

namespace App\Twig\Components;

use App\Repository\ApcNiveauRepository;
use App\Repository\PortfolioUnivRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PortfolioUnivBiblioCard
{
    public int $id;

    public ?array $competences = null;

    public function __construct(
        protected PortfolioUnivRepository $portfolioUnivRepository,
        protected ApcNiveauRepository $apcNiveauRepository,

    )
    {
    }

    public function getPortfolioUniv()
    {
        $portfolioUniv = $this->portfolioUnivRepository->find($this->id);

        $this->competences = $this->apcNiveauRepository->findByPortfolioUniversitaire($portfolioUniv->getId());

        return $portfolioUniv;
    }
}
