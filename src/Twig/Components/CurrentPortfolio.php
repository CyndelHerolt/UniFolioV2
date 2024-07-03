<?php

namespace App\Twig\Components;

use App\Entity\ApcNiveau;
use App\Repository\AnneeRepository;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\PortfolioUnivRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('CurrentPortfolio')]
final class CurrentPortfolio
{

    public function __construct(
        private readonly PortfolioUnivRepository      $portfolioUnivRepository,
        private readonly AnneeUniversitaireRepository $anneeUniversitaireRepository,
        private readonly ChartBuilderInterface        $chartBuilder,
        private readonly Security                     $security,
    )
    {
    }

    public function getChart()
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $competencesObjects = $this->getCompetences();
        foreach ($competencesObjects as $competence) {
            $competences[] = $competence->getLibelle();
        }

        $chart->setData([
            'labels' => $competences,
            'datasets' => [
                [
                    'label' => 'Evaluation des compétences',
                    'backgroundColor' => '#69387D',
                    'borderColor' => '#69387D',
                    //todo: récupérer les valeurs propres à l'évaluation des compétences dans le portfolio
                    'data' => [40, 74],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $chart;
    }

    public function getDetailsCompetence($competence)
    {
        $portfolio = $this->getCurrentPortfolio();
        $pages = $portfolio->getPages();

        foreach ($pages as $page) {
            if ($competence instanceof ApcNiveau) {
                if ($page->getApcNiveau() === $competence) {
                    $details = $page->getCritereNiveaux();
                }
            } else {
                if ($page->getApcApprentissageCritique() === $competence) {
                    $details = $page->getCritereApprentissageCritiques();
                }
            }
        }

        return $details ?? null;
    }

    public function getCompetences()
    {
        $portfolio = $this->getCurrentPortfolio();
        $pages = $portfolio->getPages();

        $competences = [];
        foreach ($pages as $page) {
            $competences[] = $page->getApcNiveau() ?? $page->getApcApprentissageCritique();
        }

        return $competences;
    }

    public function getCurrentPortfolio()
    {
        $annee = $this->anneeUniversitaireRepository->findOneBy(['active' => true]);

        $portfolio = $this->portfolioUnivRepository->findOneBy(['etudiant' => $this->security->getUser()->getEtudiant(), 'anneeUniv' => $annee]);

        return $portfolio;
    }
}
