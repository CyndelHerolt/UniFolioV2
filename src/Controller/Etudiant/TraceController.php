<?php

namespace App\Controller\Etudiant;

use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\TraceRegistry;
use App\Components\Trace\TypeTrace\TraceImage;
use App\Components\Trace\TypeTrace\TraceLien;
use App\Components\Trace\TypeTrace\TracePdf;
use App\Components\Trace\TypeTrace\TraceVideo;
use App\Controller\BaseController;
use App\Entity\Trace;
use App\Entity\Validation;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\PageRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceRepository;
use App\Repository\ValidationRepository;
use App\Service\CompetencesService;
use App\Service\DataUserSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant')]
class TraceController extends BaseController
{

    public function __construct(
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
        private readonly PortfolioUnivRepository            $portfolioUnivRepository,
        private readonly PageRepository                     $pageRepository,
        private readonly DataUserSessionService             $dataUserSessionService,
        private readonly CompetencesService                 $competencesService,
    )
    {
        parent::__construct(
            $this->dataUserSessionService,
        );
    }

    #[Route('/trace/show/{id}', name: 'app_trace_show')]
    public function show(?int $id, ?bool $edit, Request $request): Response
    {
        $edit = $request->query->get('edit') ?? false;
        $row = $request->query->get('row') ?? '';

        $trace = $this->traceRepository->find($id);

        $portfolio = $this->portfolioUnivRepository->findOneBy(['id' => $request->query->get('portfolio')]);
        $page = $this->pageRepository->findOneBy(['id' => $request->query->get('page')]);

        $type = $trace->getType();

        if ($type !== null) {
            $formType = $this->traceRegistry->getTypeTrace($type)::FORM;
            $classType = $this->traceRegistry->getTypeTrace($type)::CLASS_NAME;
        }

        // si un formulaire est soumis
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $files = $request->files->all();

            if (isset($data['trace_abstract']['libelle'])) {
                $trace->setLibelle($data['trace_abstract']['libelle']);
            }

            $this->traceRepository->save($trace, true);
        }

        return $this->render('trace/show.html.twig', [
            'trace' => $trace,
            'portfolio' => $portfolio ?? null,
            'page' => $page ?? null,
            'edit' => $edit ?? true,
            'row' => $row,
            'formType' => $formType ?? null,
            'classType' => $classType ?? null,
        ]);
    }

    #[Route('/trace/change/type', name: 'app_trace_change_type')]
    public function changeType(Request $request): Response
    {
        $type = $request->query->get('type');

        // stocker le type de trace dans la session
        $request->getSession()->set('selected_trace_type', $type);

        if ($request->query->get('trace')) {
            $trace = $this->traceRepository->find($request->query->get('trace'));
            $trace->setContenu([]);
            $trace->setType($type);
            $this->traceRepository->save($trace, true);
        }

        // récupérer le nom de la route émettrice
        $referer = $request->headers->get('referer');
        $selectedTraceType = $request->getSession()->get('selected_trace_type');

        // retourner au referer
        return $this->redirect($referer . '?type=' . $selectedTraceType);
    }

    // todo: refactor
    #[Route('/trace/show/{id}/edit', name: 'app_trace_show_edit')]
    public function showEdit(?int $id, ?string $row, ?bool $edit, Request $request): Response
    {
        $trace = $this->traceRepository->find($id);
        $row = $request->query->get('row');
        $edit = $request->query->get('edit');

        return $this->redirectToRoute('app_trace_show', ['id' => $id, 'edit' => $edit, 'row' => $row]);
    }

    // todo: refactor
    #[Route('/trace/new', name: 'app_trace_new')]
    public function new(Request $request): Response
    {
        $typesTrace = $this->traceRegistry->getTypeTraces();
        $user = $this->getUser();

        $competences = $this->competencesService->getCompetences($user);

        $trace = new Trace();
        if (isset($competences['apcNiveaux'])) {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcNiveaux']]);
        } else {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcApprentissagesCritiques']]);
        }

        // todo: code dupliqué dans edit
        // Vérifier si un type de trace a été passé en paramètre
//        $selectedTraceType = $request->query->get('type', null);
        $selectedTraceType = $request->getSession()->get('selected_trace_type', null);

        if ($selectedTraceType !== null) {
            $formType = $selectedTraceType::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $selectedTraceType::TYPE;
        } elseif ($trace->getType() !== null) {
            $selectedTraceType = $trace->getType();
            $formType = $this->traceRegistry->getTypeTrace($selectedTraceType)::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $this->traceRegistry->getTypeTrace($selectedTraceType)::TYPE;
        } else {
            $formType = null;
        }

        return $this->render('trace/form.html.twig', [
            'form' => $form->createView(),
            'typesTrace' => $typesTrace,
            'trace' => $trace,
            'typeTrace' => $typeTrace ?? null,
            'formType' => $formType ?? null,
            'selectedTraceType' => $selectedTraceType ?? null,
            'apcNiveaux' => $competences['apcNiveaux'] ?? null,
            'apcApprentissageCritiques' => $competences['apcApprentissagesCritiques'] ?? null,
            'groupedApprentissageCritiques' => $competences['groupedApprentissagesCritiques'] ?? null,

        ]);
    }

    #[Route('/trace/save', name: 'app_trace_save')]
    public function save(Request $request)
    {

        if ($request->query->get('trace') !== null) {
            $trace = $this->traceRepository->find($request->query->get('trace'));
        } else {
            $trace = new Trace();
        }

        $etudiant = $this->getUser()->getEtudiant();
        $bibliotheque = $this->bibliothequeRepository->findOneBy(['etudiant' => $etudiant, 'actif' => true]);

        $data = $request->request->all();
        $files = $request->files->all();
        $formDatas = $request->request->all()['trace_abstract'];

        // on réunit les clés des tableaux $data et $files
        $typeDatas = array_merge(array_keys($data), array_keys($files));

        // on récupère la clé du type de trace
        $typeTraceForm = array_diff($typeDatas, ['trace_abstract']);
//        dd($typeTraceForm);

        if (!empty($typeTraceForm)) {
            // on récupère la première clé du tableau
            $key = $typeTraceForm[array_key_first($typeTraceForm)];

            // on récupère le type de trace correspondant à la clé
            $typeTrace = $this->traceRegistry->getTypeTraceFromForm($key);

            if (isset($data[$key]['contenu']) || isset($files[$key]['contenu'])) {
                // on récupère le contenu du type de trace
                $contenu = $files == [] ? $data[$key]['contenu'] : $files[$key]['contenu'];

                $existingContenu = $data[$key] ?? null;
                $sauvegarde = $typeTrace->sauvegarde($contenu);
                if ($existingContenu) {
                    if ($sauvegarde['success'] === true) {
                        $content = array_merge($sauvegarde['contenu'], $existingContenu);
                    } else {
                        $this->addFlash('danger', $sauvegarde['error']);
                        // récupérer le nom de la route émettrice
                        $referer = $request->headers->get('referer');
                        // retourner au referer
                        return $this->redirect($referer);
                    }
                } else {
                    if ($sauvegarde['success'] === true) {
                        $content = $sauvegarde['contenu'];
                    } else {
                        $this->addFlash('danger', $sauvegarde['error']);
                        // récupérer le nom de la route émettrice
                        $referer = $request->headers->get('referer');
                        $selectedTraceType = $request->getSession()->get('selected_trace_type');

                        // retourner au referer
                        return $this->redirect($referer . '?type=' . $selectedTraceType);
                    }
                }
            } else {
                if (isset($data[$key])) {
                    $content = $data[$key];
                } else {
                    $content = [];
                }
            }
            $trace->setType($typeTrace::class);

        } else {
            $typeTrace = $trace->getType();
            $content = [];
            $trace->setType($typeTrace);
        }

        $trace->setContenu($content);
        $trace->setBibliotheque($bibliotheque);
        $trace->setDateCreation(new \DateTime());
        $trace->setLibelle($formDatas['libelle']);
        $trace->setContexte($formDatas['contexte']);
        if (!empty($formDatas['dateRealisation'])) {
            $trace->setDateRealisation(\DateTime::createFromFormat('m-Y', $formDatas['dateRealisation']));
        } else {
            $trace->setDateRealisation(null);
        }
        $trace->setLegende($formDatas['legende']);
        $trace->setDescription($formDatas['description']);
        $this->traceRepository->save($trace, true);

        // récupérer les compétences stockées dans la session à la construction du formulaire
        $competences = $request->getSession()->get('competences');

        if (isset($request->request->all()['trace_abstract']['competences']) && !empty($request->request->all()['trace_abstract']['competences'])) {
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

        return $this->redirectToRoute('app_biblio_traces');
    }


    // todo: refactor
    #[Route('/trace/edit/{id}', name: 'app_trace_edit')]
    public function edit(int $id, Request $request): Response
    {
        $origin = $request->query->get('origin', null);
        $trace = $this->traceRepository->find($id);
        $typesTrace = $this->traceRegistry->getTypeTraces();
        $user = $this->getUser();

        $competences = $this->competencesService->getCompetences($user);

        if (isset($competences['apcNiveaux'])) {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcNiveaux']]);
        } else {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcApprentissagesCritiques']]);
        }
        // Vérifier si un type de trace a été passé en paramètre
        $selectedTraceType = $request->query->get('type', null);
        if ($selectedTraceType !== null) {
            $formType = $selectedTraceType::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $selectedTraceType::TYPE;
        } elseif ($trace->getType() !== null) {
            $selectedTraceType = $trace->getType();
            $formType = $this->traceRegistry->getTypeTrace($selectedTraceType)::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $this->traceRegistry->getTypeTrace($selectedTraceType)::TYPE;
        } else {
            $formType = null;
        }

        // gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
        }

        return $this->render('trace/form.html.twig', [
            'form' => $form->createView(),
            'typesTrace' => $typesTrace,
            'typeTrace' => $typeTrace ?? null,
            'trace' => $trace,
            'formType' => $formType ?? null,
            'selectedTraceType' => $selectedTraceType ?? null,
            'apcNiveaux' => $competences['apcNiveaux'] ?? null,
            'apcApprentissageCritiques' => $competences['apcApprentissagesCritiques'] ?? null,
            'groupedApprentissageCritiques' => $competences['groupedApprentissagesCritiques'] ?? null,
            'origin' => $origin ?? null,
        ]);
    }

    #[Route('/trace/delete/{id}', name: 'app_trace_delete')]
    public function delete(int $id): Response
    {
        $trace = $this->traceRepository->find($id);
        $this->traceRepository->delete($trace, true);

        return $this->redirectToRoute('app_biblio_traces');
    }
}
