<?php
// src/Service/CompetencesService.php

namespace App\Service;

use App\Entity\Etudiant;
use App\Entity\User;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcApprentissageCritiqueRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CompetencesService {
    private $security;
    private $apcNiveauRepository;
    private $apcApprentissageCritiqueRepository;
    private $competenceRepository;

    public function __construct(Security $security, ApcNiveauRepository $apcNiveauRepository, ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository, ApcCompetenceRepository $competenceRepository) {
        $this->security = $security;
        $this->apcNiveauRepository = $apcNiveauRepository;
        $this->apcApprentissageCritiqueRepository = $apcApprentissageCritiqueRepository;
        $this->competenceRepository = $competenceRepository;
    }

    public function getCompetences(UserInterface $user) {

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
            // si les apcNiveaux dans niveaux ont pour actif = true
            foreach ($niveaux as $niveau) {
                if ($niveau->isActif() === true) {
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
                if ($niveau->isActif() === true) {
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
}