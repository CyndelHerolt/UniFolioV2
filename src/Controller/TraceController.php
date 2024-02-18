<?php

namespace App\Controller;

use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\TraceRegistry;
use App\Entity\Trace;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant')]
class TraceController extends AbstractController
{
    public function __construct(
        private readonly ApcCompetenceRepository $competenceRepository,
        private readonly ApcNiveauRepository $apcNiveauRepository,
        private readonly TraceRegistry $traceRegistry,
    )
    {

    }

    #[Route('/bibliotheque/traces', name: 'app_trace')]
    public function index(): Response
    {
        return $this->render('trace/index.html.twig', [
            'controller_name' => 'TraceController',
        ]);
    }

    #[Route('/trace/new', name: 'app_trace_new')]
    public function new(): Response
    {
        $typesTrace = $this->traceRegistry->getTypeTraces();
        $user = $this->getUser()->getEtudiant();

        $semestre = $user->getSemestre();
        $annee = $semestre->getAnnee();

        $dept = $user->getSemestre()->getAnnee()->getDiplome()->getDepartement();

        $groupe = $user->getGroupe();
        foreach ($groupe as $g) {
            if ($g->getTypeGroupe()->getType() === 'TD') {
                $parcours = $g->getApcParcours();
            }
        }

        if ($parcours === null) {
            $referentiel = $dept->getApcReferentiels();

            $competences = $this->competenceRepository->findBy(['apcReferentiel' => $referentiel->first()]);

            foreach ($competences as $competence) {
                $niveaux[] = $this->apcNiveauRepository->findByAnnee($competence, $annee->getOrdre());
                foreach ($niveaux as $niveau) {
                    foreach ($niveau as $niv) {
                        $competencesNiveau[] = $niv->getLibelle();
                    }
                }
            }
        } else {
            $niveaux = $this->apcNiveauRepository->findByAnneeParcours($annee, $parcours);
            foreach ($niveaux as $niveau) {
                $competencesNiveau[] = $niveau->getLibelle();
            }
        }
        $trace = new Trace();
        $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competencesNiveau]);

        return $this->render('trace/form.html.twig', [
            'form' => $form->createView(),
            'typesTrace' => $typesTrace,
        ]);
    }

}
