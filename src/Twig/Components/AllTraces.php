<?php

namespace App\Twig\Components;

use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\TraceRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('AllTraces')]
final class AllTraces
{
    use DefaultActionTrait;

    public array $competences = [];

    #[LiveProp(writable: true)]
    public array $selectedCompetences = [];

    public function __construct(
        private readonly Security $security,
        protected ApcNiveauRepository $apcNiveauRepository,
        protected ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        protected ApcCompetenceRepository $apcCompetenceRepository,
        protected BibliothequeRepository $bibliothequeRepository,
        protected TraceRepository $traceRepository,
    )
    {
        $user = $this->security->getUser()->getEtudiant();
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
            $competences = $this->apcCompetenceRepository->findBy(['apcReferentiel' => $referentiel->first()]);
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

        if (isset($apcNiveaux)) {
            $this->competences = $apcNiveaux;
        } else {
            $this->competences = $apcApprentissageCritiques;
        }
    }

    public function getAllTraces(): array
    {
        $bibliotheque = $this->bibliothequeRepository->findOneByEtudiantAnnee($this->security->getUser()->getEtudiant(), $this->security->getUser()->getEtudiant()->getSemestre()->getAnnee());

        $traces = $this->traceRepository->findBy(['bibliotheque' => $bibliotheque]);

        return $traces;
    }

}
