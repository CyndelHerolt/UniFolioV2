<?php

namespace App\Twig\Components;

use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceCompetenceRepository;
use App\Repository\TraceRepository;
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
    )
    {

    }

    public function getCompetences()
    {
        $portfolio = $this->portfolioUnivRepository->find($this->id);
        $portfolioCompetences = $this->traceCompetenceRepository->findBy(['portfolio' => $portfolio]);
        $competences = [];
        foreach ($portfolioCompetences as $portfolioCompetence) {
            if ($portfolioCompetence->getApcNiveau() !== null) {
                $competences[$portfolioCompetence->getApcNiveau()->getId()] = $portfolioCompetence->getApcNiveau();
            } elseif ($portfolioCompetence->getApcApprentissageCritique() !== null) {
                   $competences[$portfolioCompetence->getApcApprentissageCritique()->getApcNiveau()->getId()] = $portfolioCompetence->getApcApprentissageCritique();
            } else {
                // todo: gérer les erreurs
                $error = 'Erreur : compétence non trouvée';
            }
        }

        // retirer les doublons du tableau de compétences
//        $competences = array_unique($competences);

        return $competences;
    }

    public function getPortfolio()
    {
        return $this->portfolioUnivRepository->find($this->id);
    }
}
