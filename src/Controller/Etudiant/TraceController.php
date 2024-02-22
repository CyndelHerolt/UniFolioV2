<?php

namespace App\Controller\Etudiant;

use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\TraceRegistry;
use App\Components\Trace\TypeTrace\TraceImage;
use App\Components\Trace\TypeTrace\TraceLien;
use App\Components\Trace\TypeTrace\TracePdf;
use App\Components\Trace\TypeTrace\TraceVideo;
use App\Entity\Trace;
use App\Entity\Validation;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\TraceRepository;
use App\Repository\ValidationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant')]
class TraceController extends AbstractController
{

    public function __construct(
        private readonly ApcCompetenceRepository            $competenceRepository,
        private readonly ApcNiveauRepository                $apcNiveauRepository,
        private readonly ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        private readonly TraceRegistry                      $traceRegistry,
        private readonly TraceLien                          $traceLien,
        private readonly TraceImage                         $traceImage,
        private readonly TracePdf                           $tracePdf,
        private readonly TraceVideo                         $traceVideo,
        private readonly TraceRepository                    $traceRepository,
        private readonly ValidationRepository               $validationRepository,
        private readonly BibliothequeRepository             $bibliothequeRepository,
    )
    {
    }

    #[Route('/bibliotheque/traces', name: 'app_trace')]
    public function index(Request $request): Response
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

    #[Route('/trace/sauvegarde', name: 'app_trace_sauvegarde')]
    public function sauvegardeNewTrace(Request $request): Response
    {
        $data = $request->request->all();
        $files = $request->files->all();
        $formDatas = $request->request->all()['trace_abstract'];

        $etudiant = $this->getUser()->getEtudiant();
        $bibliotheque = $this->bibliothequeRepository->findOneBy(['etudiant' => $etudiant, 'actif' => true]);

        $trace = new Trace();

        if (isset($data['trace_lien'])) {
            $contenu = $data['trace_lien']['contenu'];
            $contenu = $this->traceLien->sauvegarde($contenu, null);
            $trace->setType($this->traceLien::TYPE);
        } elseif (isset($files['trace_image'])) {
            $contenu = $files['trace_image']['contenu'];
            $contenu = $this->traceImage->sauvegarde($contenu, null);
            $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
                return is_string($item);
            });
            $trace->setType($this->traceImage::TYPE);
        } elseif (isset($data['trace_video'])) {
            $contenu = $data['trace_video']['contenu'];
            $contenu = $this->traceVideo->sauvegarde($contenu, null);
            $trace->setType($this->traceVideo::TYPE);
        } elseif (isset($files['trace_pdf'])) {
            $contenu = $files['trace_pdf']['contenu'];
            $contenu = $this->tracePdf->sauvegarde($contenu, null);
            $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
                return is_string($item);
            });
            $trace->setType($this->tracePdf::TYPE);
        } else {
            $this->addFlash('danger', 'Type de trace inconnu');
            return $this->redirectToRoute('app_trace_new');
        }

        if ($contenu['success'] === false) {
            $this->addFlash('danger', $contenu['error']);
            return $this->redirectToRoute('app_trace_new');
        } else {
            $trace->setContenu($contenu['contenu']);
            $trace->setBibliotheque($bibliotheque);
            $trace->setDateCreation(new \DateTime());
            $trace->setLibelle($formDatas['libelle']);
            $trace->setContexte($formDatas['contexte']);
            $trace->setDateRealisation(\DateTime::createFromFormat('m-Y', $formDatas['dateRealisation']));
            $trace->setLegende($formDatas['legende']);
            $trace->setDescription($formDatas['description']);
            $this->traceRepository->save($trace, true);

            // récupérer les compétences stockés dans la session à la construction du formulaire
            $competences = $request->getSession()->get('competences');

            // récupérer les compétences qui ont été sélectionnées dans le formulaire
            $submittedCompetenceIds = $request->request->all()['trace_abstract']['competences'];

            $selectedCompetences = [];

            $competences = array_flip($competences);

            foreach ($submittedCompetenceIds as $id) {
                // recouper les compétences sélectionnées avec les compétences stockées dans la session pour récupérer les libellés et les id
                if (isset($competences[$id])) {
                    $selectedCompetences[$id] = $competences[$id];
                }
            }

            $apcNiveaux = [];
            $apcApprentissageCritiques = [];


            foreach ($selectedCompetences as $id => $libelle) {
                // vérifier si un ApcNiveau existe avec l'id et le libellé
                $apcNiveau = $this->apcNiveauRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
                if ($apcNiveau) {
                    $apcNiveaux[] = $apcNiveau;
                    $validation = new Validation();
                    $validation->setApcNiveau($apcNiveau);
                    $validation->setTrace($trace);
                    $validation->setEtat(0);
                    $validation->setDateCreation(new \DateTime());
                    $this->validationRepository->save($validation, true);
                } else {
                    // vérifier si un ApcApprentissageCritique existe avec l'id et le libellé
                    $apcApprentissageCritique = $this->apcApprentissageCritiqueRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
                    if ($apcApprentissageCritique) {
                        $apcApprentissageCritiques[] = $apcApprentissageCritique;
                        $validation = new Validation();
                        $validation->setApcApprentissageCritique($apcApprentissageCritique);
                        $validation->setTrace($trace);
                        $validation->setEtat(0);
                        $validation->setDateCreation(new \DateTime());
                        $this->validationRepository->save($validation, true);
                    }
                }
            }

        }

        return $this->redirectToRoute('app_trace_new');
    }
}
