<?php

namespace App\Twig\Components;

use App\Repository\AnneeUniversitaireRepository;
use App\Repository\EtudiantRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceRepository;
use App\Service\ValidationCalculService;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('BilanEtudiant')]
final class BilanEtudiant
{
    use DefaultActionTrait;

    public int $id;

    public int $nbTraces;

    public function __construct(
        private readonly EtudiantRepository           $etudiantRepository,
        private readonly PortfolioUnivRepository      $portfolioUnivRepository,
        private readonly TraceRepository              $traceRepository,
        private readonly AnneeUniversitaireRepository $anneeUniversitaireRepository,
        private readonly ValidationCalculService      $validationCalculService,
        private readonly ChartBuilderInterface        $chartBuilder
    )
    {

    }

    public function getPortfolioUniv()
    {
        $annee = $this->anneeUniversitaireRepository->findOneBy(['active' => true]);
        return $this->portfolioUnivRepository->findOneBy(['etudiant' => $this->id, 'anneeUniv' => $annee]);
    }

    public function getNbTraces()
    {
        $portfolio = $this->getPortfolioUniv();
        $traces = $this->traceRepository->findByPortfolio($portfolio);

        return count($traces);
    }

    public function getNbTraceCompetence()
    {

    }

    public function getValidationGlobal()
    {
        $validation = $this->validationCalculService->calcParEtudiant($this->getEtudiant());
        return $validation;
    }

    public function getValidationCompetence()
    {
        $portfolio = $this->getPortfolioUniv();
        $pages = $portfolio->getPages();
        $validations = [];
        foreach ($pages as $page) {
            if ($page->getApcApprentissageCritique()) {
                $couleur = $page->getApcApprentissageCritique()->getApcNiveau()->getApcCompetence()->getCouleur();
            } else {
                $couleur = $page->getApcNiveau()->getApcCompetence()->getCouleur();
            }
            $this->nbTraces = $page->getTracePages()->count();

            $validations[$page->getLibelle()][$couleur] = $this->validationCalculService->calcParPage($page);
        }

        return $validations;

    }

    public function getChartCompetence()
    {
        $validations = $this->getValidationCompetence();
        $labels = array_keys($validations);
        $data = [];
        $backgroundColor = [];

        foreach ($validations as $label => $validation) {
            foreach ($validation as $color => $score) {
                $data[] = $score;
                // Convertir la couleur de la compétence en couleur RGB
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
                        $backgroundColor[] = 'rgb(255, 255, 255)'; // Couleur par défaut
                        break;
                }
            }
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Validation des compétences',
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => 'rgb(255, 255, 255)',
                    'data' => $data,
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
            // masquer la légende
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ]);

        return $chart;
    }

    public
    function getEtudiant()
    {
        return $this->etudiantRepository->find($this->id);
    }
}
