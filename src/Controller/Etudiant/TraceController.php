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
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\PageRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceCompetenceRepository;
use App\Repository\TraceRepository;
use App\Service\CompetencesService;
use App\Service\DataUserSessionService;
use App\Service\TraceSaveService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

#[Route('/etudiant')]
class TraceController extends BaseController
{

    use TargetPathTrait;

    public function __construct(
        private readonly ApcNiveauRepository                $apcNiveauRepository,
        private readonly ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        private readonly TraceRegistry                      $traceRegistry,
        private readonly TraceLien                          $traceLien,
        private readonly TraceImage                         $traceImage,
        private readonly TracePdf                           $tracePdf,
        private readonly TraceVideo                         $traceVideo,
        private readonly TraceRepository                    $traceRepository,
        private readonly BibliothequeRepository             $bibliothequeRepository,
        private readonly PortfolioUnivRepository            $portfolioUnivRepository,
        private readonly PageRepository                     $pageRepository,
        private readonly DataUserSessionService             $dataUserSessionService,
        private readonly CompetencesService                 $competencesService,
        private readonly TraceCompetenceRepository          $traceCompetenceRepository,
        private readonly TraceSaveService                   $TraceSaveService,
    )
    {
        parent::__construct(
            $this->dataUserSessionService,
        );
    }

    #[Route('/traces', name: 'app_biblio_traces')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_ETUDIANT')) {

            return $this->render('trace/index.html.twig');

        } else {
            return $this->render('security/accessDenied.html.twig');
        }
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

        $competences = $this->competencesService->getCompetencesEtudiant($user);

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

    //todo: transformer en service
    #[Route('/trace/save', name: 'app_trace_save')]
    public function save(Request $request)
    {

        if ($request->query->get('trace') !== null) {
            $trace = $this->traceRepository->find($request->query->get('trace'));
        } else {
            $trace = new Trace();
        }

        $this->TraceSaveService->save($trace, $request);

        return $this->redirectToRoute('app_biblio_traces');
    }


    // todo: refactor
    #[Route('/trace/edit/{id}', name: 'app_trace_edit')]
    public function edit(int $id, Request $request): Response
    {
        $trace = $this->traceRepository->find($id);
        $typesTrace = $this->traceRegistry->getTypeTraces();
        $user = $this->getUser();

        $competences = $this->competencesService->getCompetencesEtudiant($user);

        if (isset($competences['apcNiveaux'])) {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcNiveaux']]);
        } else {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcApprentissagesCritiques']]);
        }
        // Vérifier si un type de trace a été passé en paramètre
        $selectedTraceType = $request->query->get('type', null);
        if ($trace->getType() !== null) {
            $selectedTraceType = $trace->getType();
            $formType = $this->traceRegistry->getTypeTrace($selectedTraceType)::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $this->traceRegistry->getTypeTrace($selectedTraceType)::TYPE;
        } elseif ($selectedTraceType !== null) {
            $formType = $selectedTraceType::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $selectedTraceType::TYPE;
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
