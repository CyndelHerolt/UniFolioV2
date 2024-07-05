<?php

namespace App\Twig\Components;

use App\Entity\Etudiant;
use App\Entity\Semestre;
use App\Repository\DepartementRepository;
use App\Repository\EtudiantRepository;
use App\Repository\SemestreRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('BilanEtudiant')]
final class BilanEtudiant
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    /** @var Etudiant[] */
    public array $allEtudiants = [];

    #[LiveProp(writable: true)]
    /** @var Etudiant[] */
    public array $filteredEtudiants = [];

    public array $allSemestres = [];

    #[LiveProp(writable: true)]
    public ?Semestre $selectedSemestre = null;

    #[LiveProp(writable: true)]
    public ?string $search = '';

    public function __construct(
        private readonly SemestreRepository    $semestreRepository,
        private readonly EtudiantRepository    $etudiantRepository,
        private readonly DepartementRepository $departementRepository,
        private readonly Security              $security,
    )
    {
    }

    public function getSemestres(): array
    {
        $departement = $this->departementRepository->findDepartementEnseignantDefaut($this->security->getUser()->getEnseignant());
        $this->allSemestres = $this->semestreRepository->findByDepartementActif($departement);

        return $this->allSemestres;
    }

    #[LiveAction]
    public function changeSemestre(#[LiveArg] ?int $semestreId): void
    {
        $this->selectedSemestre = $this->semestreRepository->find($semestreId);
        $this->allEtudiants = $this->etudiantRepository->findBySemestre($this->selectedSemestre);
    }

    #[LiveAction]
    public function updateSearch(#[LiveArg] string $search): void
    {
        $this->search = $search;
        $this->filteredEtudiants = $this->getFilteredEtudiants();
    }

    #[LiveAction]
    public function getFilteredEtudiants(): array
    {

//        $this->allEtudiants = $this->getEtudiants();

        if (empty($this->search)) {
            return $this->allEtudiants;
        } else {
            return array_filter($this->getEtudiants(), function (Etudiant $etudiant) {
                return str_contains(strtolower($etudiant->getNom()), strtolower($this->search))
                    || str_contains(strtolower($etudiant->getPrenom()), strtolower($this->search));
            });
        }
    }

    public function getEtudiants(): array
    {
        if ($this->selectedSemestre === null) {
            $semestres = $this->getSemestres();
            foreach ($semestres as $semestre) {
                $etudiants = $this->etudiantRepository->findBySemestre($semestre);
                foreach ($etudiants as $etudiant) {
                    $this->allEtudiants[] = $etudiant;
                }
            }
        }

        return $this->allEtudiants;
    }
}
