<?php

namespace App\Twig\Components;

use App\Entity\Departement;
use App\Entity\PortfolioUniv;
use App\Entity\Semestre;
use App\Repository\DepartementRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\SemestreRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class AllPortfoliosEval
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    /** @var PortfolioUniv[] */
    public array $allPortfolios = [];

    public ?Departement $departement = null;

    public array $semestres = [];

    #[LiveProp(writable: true)]
    public ?Semestre $selectedSemestre = null;

    public array $groupes = [];

    #[LiveProp(writable: true)]
    public array $selectedGroupes = [];

    public array $etudiants = [];

    #[LiveProp(writable: true)]
    public array $selectedEtudiants = [];

    public array $competences = [];

    #[LiveProp(writable: true)]
    public array $selectedCompetences = [];

    public array $etats = [];

    #[LiveProp(writable: true)]
    public int $selectedEtat = 0;


    public function __construct(
        private readonly Security                $security,
        private readonly PortfolioUnivRepository $portfolioUnivRepository,
        private readonly DepartementRepository   $departementRepository,
        private readonly SemestreRepository      $semestreRepository,
    )
    {
        $user = $this->security->getUser()->getEnseignant();
        $this->departement = $this->departementRepository->findDepartementEnseignantDefaut($user);

        $this->allPortfolios = $this->getAllPortfolios();
    }

    public function getAllSemestres()
    {
        $this->semestres = $this->semestreRepository->findByDepartementActif($this->departement);

        return $this->semestres;
    }

    #[LiveAction]
    public function getSelectedSemestre(#[LiveArg] ?int $id = null)
    {
        if ($id !== null) {
            $this->selectedSemestre = $this->semestreRepository->find($id);
        }

        $this->getAllPortfolios();
    }

    public function getAllPortfolios()
    {

        $portfolios = $this->portfolioUnivRepository->findByFilters($this->departement, $this->selectedSemestre, $this->selectedGroupes, $this->selectedEtudiants, $this->selectedCompetences);

        return $portfolios;
    }
}
