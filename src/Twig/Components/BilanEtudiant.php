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
use Symfony\UX\TwigComponent\Attribute\PostMount;

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
    public function updateSearch(#[LiveArg] string $search): void
    {
        $this->search = $search;
        $this->filteredEtudiants = $this->getFilteredEtudiants(); // Mettez à jour filteredEtudiants directement ici.
    }

    public function getFilteredEtudiants(): array
    {
        $departement = $this->departementRepository->findDepartementEnseignantDefaut($this->security->getUser()->getEnseignant());
        $etudiants = $this->etudiantRepository->findByDepartement($departement);

        if (empty($this->search)) {
            $this->filteredEtudiants = $this->allEtudiants;
        } else {
            $this->filteredEtudiants = array_filter($etudiants, function (Etudiant $etudiant) {
                $searchLower = strtolower($this->search);
                return strpos(strtolower($etudiant->getNom()), $searchLower) !== false
                    || strpos(strtolower($etudiant->getPrenom()), $searchLower) !== false;
            });
        }
        return $this->filteredEtudiants; // Retourne filteredEtudiants pour une utilisation immédiate si nécessaire.
    }

    public function getEtudiants(): array
    {

        $semestres = $this->getSemestres();
        foreach ($semestres as $semestre) {
            $etudiants = $this->etudiantRepository->findBySemestre($semestre);
            foreach ($etudiants as $etudiant) {
                $this->allEtudiants[] = $etudiant;
            }
        }

        return $this->allEtudiants;
    }
}
