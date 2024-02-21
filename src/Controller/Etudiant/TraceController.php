<?php

namespace App\Controller\Etudiant;

use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\TraceRegistry;
use App\Entity\Trace;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            // -----------------------------------------------------------------------------


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
            // -----------------------------------------------------------------------------
        }



        $trace = new Trace();
        if (isset($apcNiveaux)) {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $apcNiveaux]);
        } else {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $apcApprentissageCritiques]);
        }
        // Vérifier si un type de trace est stocké dans la session
        //  $selectedTraceType = $request->getSession()->get('selected_trace_type');
        $selectedTraceType = $request->query->get('type', null);
        if ($selectedTraceType !== null) {
            $formType = $selectedTraceType::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $selectedTraceType::TYPE;
        } else {
            $formType = null;
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


        return $this->render('trace/form.html.twig', [
            'form' => $form->createView(),
            'typesTrace' => $typesTrace,
            'trace' => $trace,
            'type' => $typeTrace ?? null,
            'formType' => $formType ?? null,
            'selectedTraceType' => $selectedTraceType ?? null,
            'apcNiveaux' => $apcNiveaux ?? null,
            'apcApprentissageCritiques' => $apcApprentissageCritiques ?? null,
            'groupedApprentissageCritiques' => $groupedApprentissageCritiques,

        ]);
    }

    #[Route('/trace/new/{type}', name: 'app_trace_new_type')]
    public function newType($type, Request $request): Response
    {
        // Stocker le type de trace dans la session
        $request->getSession()->set('selected_trace_type', $type);

        return $this->redirectToRoute('app_trace_new', ['type' => $type]);
    }
}
