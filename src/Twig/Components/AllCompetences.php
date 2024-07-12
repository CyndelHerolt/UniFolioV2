<?php

namespace App\Twig\Components;

use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcNiveau;
use App\Entity\Departement;
use App\Repository\AnneeRepository;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\DepartementRepository;
use App\Repository\SemestreRepository;
use App\Service\CompetencesService;
use App\Service\ValidationCalculService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent('AllCompetences')]
final class AllCompetences
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, hydrateWith: 'getAllCompetences', dehydrateWith: 'getAllCompetences')]
    public array $allCompetences = [];

    #[LiveProp(writable: true, hydrateWith: 'getAllCompetencesSemestre', dehydrateWith: 'getAllCompetencesSemestre')]
    public array $allCompetencesSemestre = [];

    public array $semestres = [];

    public ?Departement $departement = null;

    public function __construct(
        private readonly Security                           $security,
        private readonly DepartementRepository              $departementRepository,
        private readonly ApcNiveauRepository                $apcNiveauRepository,
        private readonly ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        private readonly ValidationCalculService            $validationCalculService,
        private readonly CompetencesService                 $competencesService,
        private readonly SemestreRepository                 $semestreRepository,
        private readonly AnneeRepository                    $anneeRepository,
        private readonly ChartBuilderInterface              $chartBuilder
    )
    {
        $user = $this->security->getUser();
        if ($user->getEtudiant()) {
            $this->departement = $user->getEtudiant()->getSemestre()->getAnnee()->getDiplome()->getDepartement();
        } elseif ($user->getEnseignant()) {
            $this->departement = $this->departementRepository->findDepartementEnseignantDefaut($user->getEnseignant());
        }

        $this->allCompetences = $this->getAllCompetences();
    }

    #[PostMount]
    public function init()
    {
    }

    public function getValidationCompetence($competence)
    {
        $validations = $this->validationCalculService->calcParCompetence($competence);

        return $validations;
    }

    public function getAllCompetences(): array
    {
        $this->allCompetences = $this->competencesService->getCompetencesDepartement($this->departement);

        // ajouter la validation en key du tableau pour chaque compétence
        foreach ($this->allCompetences as $competence) {
            $competence->validation = $this->getValidationCompetence($competence);
        }

        return $this->allCompetences;
    }

    public function getAllCompetencesSemestre()
    {
        $this->semestres = $this->semestreRepository->findByDepartementActif($this->departement);

        foreach ($this->semestres as $semestre) {
            $this->allCompetencesSemestre[$semestre->getLibelle()] = $this->competencesService->getCompetencesSemestre($semestre);
        }

        return $this->allCompetencesSemestre;
    }

    public function getChart()
    {
        $competences = $this->getAllCompetences();
        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach ($competences as $competence) {
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

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                    'borderWidth' => 1,
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
}
