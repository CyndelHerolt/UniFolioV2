<?php

namespace App\Twig\Components;

use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcNiveau;
use App\Entity\Departement;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\DepartementRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent('AllCompetences')]
final class AllCompetences
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, hydrateWith: 'getAllCompetences', dehydrateWith: 'getAllCompetences')]
//    /** @var ApcNiveau[] */
    public array $allCompetences = [];

    public ?Departement $departement = null;

    public function __construct(
        private readonly Security                           $security,
        private readonly DepartementRepository              $departementRepository,
        private readonly ApcNiveauRepository                $apcNiveauRepository,
        private readonly ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
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

    public function getAllCompetences(): array
    {
        if ($this->departement->getOptCompetence() === 1) {
            $this->allCompetences = $this->apcNiveauRepository->findByDepartement($this->departement);
        } elseif ($this->departement->getOptCompetence() === 0) {
            $this->allCompetences = $this->apcApprentissageCritiqueRepository->findByDepartement($this->departement);
        }

        return $this->allCompetences;
    }
}
