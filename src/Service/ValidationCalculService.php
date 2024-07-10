<?php

namespace App\Service;

use App\Entity\ApcNiveau;
use App\Entity\Enseignant;
use App\Entity\Etudiant;
use App\Entity\Page;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\CritereApprentissageCritiqueRepository;
use App\Repository\CritereNiveauRepository;
use App\Repository\DepartementRepository;
use App\Repository\PortfolioUnivRepository;
use Symfony\Bundle\SecurityBundle\Security;

class ValidationCalculService
{

    public function __construct(
        private readonly AnneeUniversitaireRepository           $anneeUniversitaireRepository,
        private readonly PortfolioUnivRepository                $portfolioUnivRepository,
        private readonly DepartementRepository                  $departementRepository,
        private readonly CritereNiveauRepository                $critereNiveauRepository,
        private readonly CritereApprentissageCritiqueRepository $critereApprentissageCritiqueRepository,
        private readonly Security                               $security
    )
    {
    }

    public function calcParEtudiant(Etudiant $etudiant)
    {
        $annee = $this->anneeUniversitaireRepository->findOneBy(['active' => true]);
        $portfolio = $this->portfolioUnivRepository->findOneBy(['etudiant' => $etudiant, 'anneeUniv' => $annee]);
        $pages = $portfolio->getPages();

        $validation = [];
        foreach ($pages as $page) {
            $validation[] = $this->calcParPage($page);
        }
        // faire une moyenne des validations par page
        return array_sum($validation) / count($validation);
    }

    public function calcParPage(?Page $page)
    {
        $user = $this->security->getUser()->getEtudiant() ?? $this->security->getUser()->getEnseignant();
        if ($user instanceof Enseignant) {
            $departement = $this->departementRepository->findDepartementEnseignantDefaut($this->security->getUser()->getEnseignant());
        } else {
            $departement = $user->getSemestre()->getAnnee()->getDiplome()->getDepartement();
        }
        if ($departement->getOptCompetence() === 1) {
            $validations = $this->critereNiveauRepository->findBy(['page' => $page]);
        } else {
            $validations = $this->critereApprentissageCritiqueRepository->findBy(['page' => $page]);
        }

        $sum = 0;
        foreach ($validations as $validation) {
            $sum += $validation->getValeur();
        }

        return $sum;
    }

    public function calcParCompetence($competence)
    {
        if ($competence instanceof ApcNiveau) {
            $validations = $this->critereNiveauRepository->findBy(['apcNiveau' => $competence]);
        } else {
            $validations = $this->critereApprentissageCritiqueRepository->findBy(['apprentissageCritique' => $competence]);
        }
        $sum = 0;
        foreach ($validations as $validation) {
            $sum += $validation->getValeur();
        }


        return $sum;
    }

    public function calcGlobal()
    {

    }

}