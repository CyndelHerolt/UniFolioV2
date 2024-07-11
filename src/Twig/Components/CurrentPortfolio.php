<?php

namespace App\Twig\Components;

use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcNiveau;
use App\Repository\AnneeRepository;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\PortfolioUnivRepository;
use App\Service\ValidationCalculService;
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
        private readonly ValidationCalculService      $validationCalculService
    )
    {
    }

    public function getChart()
    {
        $competencesObjects = $this->getCompetences();
        dump($competencesObjects);
        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach ($competencesObjects as $competence) {
            $labels[] = $competence->getLibelle();
            $data[] = $competence->validation;
            $couleurs = [];

            if ($competence instanceof ApcApprentissageCritique) {
                $couleurs[] = $competence->getApcNiveau()->getApcCompetence()->getCouleur();
            } else {
                $couleurs[] = $competence->getApcCompetence()->getCouleur();
            }
            foreach ($couleurs as $color) {
                switch ($color) {
                    case 'c1':
                        $backgroundColor[] = 'rgba(156, 43, 38, 0.6)';
                        break;
                    case 'c2':
                        $backgroundColor[] = 'rgba(208, 119, 64, 0.6)';
                        break;
                    case 'c3':
                        $backgroundColor[] = 'rgba(229, 185, 77, 0.6)';
                        break;
                    case 'c4':
                        $backgroundColor[] = 'rgba(65, 108, 63, 0.6)';
                        break;
                    case 'c5':
                        $backgroundColor[] = 'rgba(43, 76, 118, 0.6)';
                        break;
                    case 'c6':
                        $backgroundColor[] = 'rgba(127, 31, 83, 0.6)';
                        break;
                    default:
                        $backgroundColor[] = 'rgb(255, 255, 255)';
                        break;
                }
            }
        }
        dump($backgroundColor);
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'x' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 20,
                ],
                'y' => [
                    'ticks' => [
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                ]
            ],
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Moyennes des resultats par compétence',
                    'font' => [
                        'size' => 20,
                        'weight' => 'bold',
                    ],
                ],
            ],
        ]);

        return $chart;
    }

    public function getValidationCompetence($competence)
    {
        $validations = $this->validationCalculService->calcParCompetence($competence);

        return $validations;
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

    public function getMoyenneCompetence($competence)
    {
        $moyenne = $this->validationCalculService->calcParCompetence($competence);

        return $moyenne;
    }

    public function getCompetences()
    {
        $portfolio = $this->getCurrentPortfolio();
        $pages = $portfolio->getPages();

        $competences = [];
        foreach ($pages as $page) {
            $competences[] = $page->getApcNiveau() ?? $page->getApcApprentissageCritique();
        }

        foreach ($competences as $competence) {
            $competence->validation = $this->getValidationCompetence($competence);
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
