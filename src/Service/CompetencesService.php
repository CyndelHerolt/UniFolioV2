<?php
// src/Service/CompetencesService.php

namespace App\Service;

use App\Entity\Departement;
use App\Entity\Etudiant;
use App\Entity\User;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\GroupeRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CompetencesService
{
    private $security;
    private $apcNiveauRepository;
    private $apcApprentissageCritiqueRepository;
    private $competenceRepository;
    private $groupeRepository;

    public function __construct(
        Security                           $security,
        ApcNiveauRepository                $apcNiveauRepository,
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        ApcCompetenceRepository            $competenceRepository,
        GroupeRepository                   $groupeRepository
    )
    {
        $this->security = $security;
        $this->apcNiveauRepository = $apcNiveauRepository;
        $this->apcApprentissageCritiqueRepository = $apcApprentissageCritiqueRepository;
        $this->competenceRepository = $competenceRepository;
        $this->groupeRepository = $groupeRepository;
    }

    public function getCompetencesEtudiant(UserInterface $user)
    {
        $user = $user->getEtudiant();
        $semestre = $user->getSemestre();
        $annee = $semestre->getAnnee();

        $dept = $user->getSemestre()->getAnnee()->getDiplome()->getDepartement();

        $groupe = $user->getGroupe();
        foreach ($groupe as $g) {
            if ($g->getTypeGroupe()->getType() === 'TD') {
                $parcours = $g->getApcParcours();
            }
        }

        $apcApprentissageCritiques = [];
        $apcNiveaux = [];

        if ($parcours === null) {
            // ------------récupère tous les apcNiveau de l'année -------------------------
            $referentiel = $dept->getApcReferentiels();
            $competences = $this->competenceRepository->findBy(['apcReferentiel' => $referentiel->first()]);
            $niveaux = [];
            foreach ($competences as $competence) {
                $niveaux = array_merge($niveaux, $this->apcNiveauRepository->findByAnnee($competence, $annee->getOrdre()));
            }
            foreach ($niveaux as $niveau) {
                if ($dept->getOptCompetence() === 1) {
                    $apcNiveaux[] = $niveau;
                } else {
                    // on stocke tous les apcNiveaux.apcApprentissageCritiques dans un tableau
                    foreach ($niveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {
                        $apcApprentissageCritiques[] = $apcApprentissageCritique;
                    }
                }
            }
        } else {
            // ------------récupère tous les apcNiveau de l'année -------------------------
            $niveaux = $this->apcNiveauRepository->findByAnneeParcours($annee, $parcours);
            foreach ($niveaux as $niveau) {
                if ($dept->getOptCompetence() === 1) {
                    $apcNiveaux[] = $niveau;
                } else {
                    // on stocke tous les apcNiveaux.apcApprentissageCritiques dans un tableau
                    foreach ($niveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {
                        $apcApprentissageCritiques[] = $apcApprentissageCritique;
                    }
                }
            }
        }

        $groupedApprentissageCritiques = [];
        foreach ($apcApprentissageCritiques as $ac) {
            $niveauId = $ac->getApcNiveau()->getId();
            if (!isset($groupedApprentissageCritiques[$niveauId])) {
                $groupedApprentissageCritiques[$niveauId] = [
                    'niveau' => $ac->getApcNiveau(),
                    'critiques' => [],
                ];
            }
            $groupedApprentissageCritiques[$niveauId]['critiques'][] = $ac;
        }

        return [
            'apcApprentissagesCritiques' => $apcApprentissageCritiques,
            'apcNiveaux' => $apcNiveaux,
            'groupedApprentissagesCritiques' => $groupedApprentissageCritiques,
        ];
    }

    public function getCompetencesDepartement($departement)
    {

        $niveaux = $this->apcNiveauRepository->findByDepartement($departement);

        $apcNiveaux = [];
        $apcApprentissagesCritiques = [];
        foreach ($niveaux as $niveau) {
            $apcNiveaux[] = $niveau;
            foreach ($niveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {
                $apcApprentissagesCritiques[] = $apcApprentissageCritique;
            }
        }
        if ($departement->getOptCompetence() === 1) {
            return $apcNiveaux;
        } else {
            return $apcApprentissagesCritiques;
        }
    }

    public function getCompetencesSemestre($semestre)
    {
        $departement = $semestre->getAnnee()->getDiplome()->getDepartement();
        $referentiel = $departement->getApcReferentiels()->first();
        $competences = $this->competenceRepository->findBy(['apcReferentiel' => $referentiel]);

        $groupes = $this->groupeRepository->findBySemestre($semestre);
//        if ($semestre->getAnnee()->getOrdre() === 3) {
//            dd($groupes);
//        }
        $niveaux = [];
        $competencesNiveau = [];
        foreach ($groupes as $groupe) {
            if ($groupe->getApcParcours() !== null) {
                if ($groupe->getApcParcours() === $semestre->getAnnee()->getDiplome()->getApcParcours()) {
                    $parcours = $groupe->getApcParcours();
                    $annee = $semestre->getAnnee();
                    $niveaux = $this->apcNiveauRepository->findByAnneeParcours($annee, $parcours);
                    $competencesNiveau = $niveaux;
                }
            } elseif($groupe->getApcParcours() === null) {
                foreach ($competences as $competence) {
                    $niveaux = $this->apcNiveauRepository->findByAnnee($competence, $semestre->getAnnee()->getOrdre());
                    $competencesNiveau = array_merge($competencesNiveau, $niveaux);
                    //supprimer les doublons du tableau
                    $competencesNiveau = array_unique($competencesNiveau, SORT_REGULAR);
                }
            }
            $niveaux = $competencesNiveau;
        }

        return $niveaux;
    }

    public function getCompetencesPortfolio($portfolio)
    {
        $pages = $portfolio->getPages();
        foreach ($pages as $page) {
            if ($page->getApcNiveau()) {
                $apcNiveaux[] = $page->getApcNiveau();
            } else {
                $apcApprentissagesCritiques[] = $page->getApcApprentissageCritique();
            }
        }

        return [
            'apcApprentissagesCritiques' => $apcApprentissagesCritiques ?? null,
            'apcNiveaux' => $apcNiveaux ?? null,
        ];
    }
}