<?php

namespace App\Twig\Components;

use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceCompetenceRepository;
use App\Repository\TraceRepository;
use App\Service\CompetencesService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('PortfolioEvalTable')]
final class PortfolioEvalTable
{
    use DefaultActionTrait;

    public int $id;

    public function __construct(
        private readonly PortfolioUnivRepository   $portfolioUnivRepository,
        private readonly TraceRepository           $traceRepository,
        private readonly TraceCompetenceRepository $traceCompetenceRepository,
        private readonly CompetencesService        $competencesService,
    )
    {

    }

    public function getCompetences()
    {
        $portfolio = $this->portfolioUnivRepository->find($this->id);
        $competences = $this->competencesService->getCompetencesPortfolio($portfolio);

        if ($competences['apcNiveaux']) {
            $competences = $competences['apcNiveaux'];
        } else {
            $competences = $competences['apcApprentissageCritiques'];
        }


        return $competences;
    }

    public function getPortfolio()
    {
        return $this->portfolioUnivRepository->find($this->id);
    }
}
