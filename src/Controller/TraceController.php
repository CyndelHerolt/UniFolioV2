<?php

namespace App\Controller;

use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\TraceRegistry;
use App\Entity\Trace;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\TraceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant')]
class TraceController extends AbstractController
{

    public function __construct(
        private readonly ApcCompetenceRepository $competenceRepository,
        private readonly ApcNiveauRepository     $apcNiveauRepository,
        private readonly TraceRegistry           $traceRegistry,
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
    public function new(Request $request): Response
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

        // Vérifier si un type de trace est stocké dans la session
        $selectedTraceType = $request->getSession()->get('selected_trace_type');
        if ($selectedTraceType) {
            $formType = $selectedTraceType::FORM;
            $formType = $this->createForm($formType, $trace);
        }

        return $this->render('trace/form.html.twig', [
            'form' => $form->createView(),
            'typesTrace' => $typesTrace,
            'trace' => $trace,
            'type' => $type ?? null,
            'formType' => $formType->createView() ?? null,
        ]);
    }

    #[Route('/trace/new/{type}', name: 'app_trace_new_type')]
    public function newType($type, Request $request): Response
    {
        $trace = new Trace();
        $formType = $type::FORM;
        $formType = $this->createForm($formType, $trace);

        // Stocker le type de trace dans la session
        $request->getSession()->set('selected_trace_type', $type);

        return $this->render('trace/_form_type.html.twig', [
            'formType' => $formType->createView(),
            'type' => $type,
        ]);
    }
}
