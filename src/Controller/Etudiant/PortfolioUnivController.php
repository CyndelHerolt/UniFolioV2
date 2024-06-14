<?php

namespace App\Controller\Etudiant;

use App\Classes\DataUserSession;
use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\TraceRegistry;
use App\Components\Trace\TypeTrace\TraceImage;
use App\Components\Trace\TypeTrace\TraceLien;
use App\Components\Trace\TypeTrace\TracePdf;
use App\Components\Trace\TypeTrace\TraceVideo;
use App\Controller\BaseController;
use App\Entity\Page;
use App\Entity\PortfolioUniv;
use App\Entity\Trace;
use App\Entity\TracePage;
use App\Entity\Validation;
use App\Form\PageType;
use App\Form\PortfolioUnivType;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\PageRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TracePageRepository;
use App\Repository\TraceRepository;
use App\Repository\ValidationRepository;
use App\Service\CompetencesService;
use App\Service\DataUserSessionService;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant/portfolio/univ')]
class PortfolioUnivController extends BaseController
{
    public function __construct(
        protected PortfolioUnivRepository            $portfolioUnivRepository,
        protected PageRepository                     $pageRepository,
        protected TraceRepository                    $traceRepository,
        protected TracePageRepository                $tracePageRepository,
        protected TraceRegistry                      $traceRegistry,
        protected ApcNiveauRepository                $apcNiveauRepository,
        protected ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        protected ApcCompetenceRepository            $competenceRepository,
        private readonly ValidationRepository        $validationRepository,
        private readonly BibliothequeRepository      $bibliothequeRepository,
        protected DataUserSessionService             $dataUserSessionService,
        private readonly CompetencesService          $competencesService
    )
    {
        parent::__construct(
            $dataUserSessionService
        );
    }

    #[Route('/', name: 'app_biblio_portfolio_univ')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_ETUDIANT')) {

            return $this->render('portfolio_univ/index.html.twig', [
                'controller_name' => 'PortfolioUnivController',
            ]);

        } else {
            return $this->render('security/accessDenied.html.twig');
        }
    }

    #[Route('/show/{id}', name: 'app_portfolio_univ_show', defaults: ["page" => 1])]
    public function show(PortfolioUniv $portfolio, Request $request): Response
    {
        $pages = $this->pageRepository->findBy(['portfolio' => $portfolio]);

        $page = $request->query->get('page', 1);

        $adapter = new ArrayAdapter($pages);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(1);
        $pagerfanta->setCurrentPage($page);

        $currentPage = $this->pageRepository->find($pagerfanta->getCurrentPageResults()[0]->getId());
        $tracesPage = $this->traceRepository->findInPage($currentPage);

        $user = $this->getUser();

        $competences = $this->competencesService->getCompetences($user);

        return $this->render('portfolio_univ/show.html.twig', [
            'portfolio' => $portfolio,
            'pages' => $pagerfanta,
            'tracesPage' => $tracesPage,
            'apcNiveaux' => $competences['apcNiveaux'] ?? null,
            'apcApprentissageCritiques' => $competences['apcApprentissagesCritiques'] ?? null,
            'groupedApprentissageCritiques' => $competences['groupedApprentissagesCritiques'] ?? null,
        ]);
    }

    #[Route('/new', name: 'app_portfolio_univ_new')]
    public function create(Request $request): Response
    {
        $portfolio = new PortfolioUniv();

        $form = $this->createForm(PortfolioUnivType::class, $portfolio);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $portfolio->setLibelle($form->get('libelle')->getData());
            $portfolio->setDescription($form->get('description')->getData());
            $imageFile = $form['banniere']->getData();
            if ($imageFile) {
                $imageFileName = uniqid() . '.' . $imageFile->guessExtension();
                //Vérifier si le fichier est au bon format
                if (in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                    $imageFile->move($_ENV['PATH_FILES'], $imageFileName);
                    $portfolio->setBanniere($_ENV['SRC_FILES'] . '/' . $imageFileName);
                } elseif (!in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                    $this->addFlash('danger', 'L\'image doit être au format jpg, jpeg, png, gif, svg ou webp');
                }
            } else {
                $portfolio->setBanniere($_ENV['SRC_FILES'] . '/banniere.jpg');
            }
            $portfolio->setEtudiant($this->getUser()->getEtudiant());
            $portfolio->setAnnee($this->getUser()->getEtudiant()->getSemestre()->getAnnee());
            $portfolio->setVisibilite($form->get('visibilite')->getData());
            $portfolio->setDateCreation(new \DateTime('now'));
            $portfolio->setOptSearch($form->get('optSearch')->getData());

            $this->portfolioUnivRepository->save($portfolio, true);

            return $this->redirectToRoute('app_portfolio_univ_edit_portfolio', ['id' => $portfolio->getId()]);
        }

        return $this->render('portfolio_univ/form.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_portfolio_univ_edit_portfolio')]
    public function editPortfolio(Request $request, ?int $id): Response
    {
        $portfolio = $this->portfolioUnivRepository->find($id);

        $form = $this->createForm(PortfolioUnivType::class, $portfolio);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $portfolio->setLibelle($form->get('libelle')->getData());
            $portfolio->setDescription($form->get('description')->getData());
            $imageFile = $form['banniere']->getData();
            if ($imageFile) {
                $imageFileName = uniqid() . '.' . $imageFile->guessExtension();
                //Vérifier si le fichier est au bon format
                if (in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                    $imageFile->move($_ENV['PATH_FILES'], $imageFileName);
                    $portfolio->setBanniere($_ENV['SRC_FILES'] . '/' . $imageFileName);
                } elseif (!in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                    $this->addFlash('danger', 'L\'image doit être au format jpg, jpeg, png, gif, svg ou webp');
                }
            } else {
                $portfolio->setBanniere($_ENV['SRC_FILES'] . '/banniere.jpg');
            }
            $portfolio->setVisibilite($form->get('visibilite')->getData());
            $portfolio->setOptSearch($form->get('optSearch')->getData());

            $this->portfolioUnivRepository->save($portfolio, true);
        }

        return $this->render('portfolio_univ/edit.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form->createView(),
            'step' => 'portfolio'
        ]);
    }

    #[Route('/edit/{id}/new/page', name: 'app_portfolio_univ_edit_new_page')]
    public function editPortfolioNewPage(?int $id): Response
    {
        $portfolio = $this->portfolioUnivRepository->find($id);
        $page = new Page();
        $page->setPortfolio($portfolio);
        $allPages = $this->pageRepository->findBy(['portfolio' => $portfolio]);
        $page->setOrdre(count($allPages) + 1);
        $page->setLibelle('Nouvelle page');
        $this->pageRepository->save($page, true);

        return $this->redirectToRoute('app_portfolio_univ_edit_page', ['id' => $page->getId()]);
    }

    #[Route('/edit/page/{id}', name: 'app_portfolio_univ_edit_page')]
    public function editPortfolioPage(Request $request, ?int $id): Response
    {
        $user = $this->getUser()->getEtudiant();
        $page = $this->pageRepository->find($id);
        $portfolio = $page->getPortfolio();
        $edit = $request->query->get('edit', false);
        $traces = $this->traceRepository->findNotInPage($page, $user->getBibliotheques());
        $tracesPage = $this->traceRepository->findInPage($page);

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page->setLibelle($form->get('libelle')->getData());
            $page->setDescription($form->get('description')->getData());
            $this->pageRepository->save($page, true);
        }

        if ($edit) {
            return $this->render('partials/page/_form.html.twig', [
                'page' => $page,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('portfolio_univ/edit.html.twig', [
            'page' => $page,
            'step' => 'page',
            'portfolio' => $portfolio,
            'edit' => $edit,
            'traces' => $traces,
            'tracesPage' => $tracesPage
        ]);
    }

    #[Route('/edit/page/delete/{id}', name: 'app_portfolio_univ_edit_delete_page')]
    public function editPortfolioDeletePage(?int $id): Response
    {
        $page = $this->pageRepository->find($id);
        $this->pageRepository->delete($page, true);

        return $this->redirectToRoute('app_portfolio_univ_edit_portfolio', ['id' => $page->getPortfolio()->getId()]);
    }

    #[Route('/edit/page/{id}/add/trace', name: 'app_portfolio_univ_edit_add_trace')]
    public function editPortfolioAddTrace(Request $request, ?int $id): Response
    {
        $page = $this->pageRepository->find($id);
        $edit = false;

        $typesTrace = $this->traceRegistry->getTypeTraces();
        $user = $this->getUser();

        $competences = $this->competencesService->getCompetences($user);

        $trace = new Trace();
        if (isset($competences['apcNiveaux'])) {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcNiveaux']]);
        } else {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcApprentissagesCritiques']]);
        }

        $selectedTraceType = $request->getSession()->get('selected_trace_type', null);

        if ($selectedTraceType !== null) {
            $formType = $selectedTraceType::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $selectedTraceType::TYPE;
        } else {
            $typeTrace = null;
            $formType = null;
        }


        return $this->render('trace/form.html.twig', [
            'trace' => $trace,
            'form' => $form->createView(),
            'formType' => $formType,
            'typeTrace' => $typeTrace,
            'page' => $page,
            'edit' => $edit,
            'typesTrace' => $typesTrace,
            'selectedTraceType' => $selectedTraceType,
            'apcNiveaux' => $competences['apcNiveaux'] ?? null,
            'apcApprentissageCritiques' => $competences['apcApprentissagesCritiques'] ?? null,
            'groupedApprentissageCritiques' => $competences['groupedApprentissagesCritiques'] ?? null,
        ]);
    }

    #[Route('/edit/page/{page}/trace/type', name: 'app_portfolio_univ_edit_trace_type')]
    public function editPortfolioTraceType(Request $request, ?int $page): Response
    {
        $type = $request->query->get('type');
        $page = $this->pageRepository->find($page);

        // stocker le type de trace dans la session
        $request->getSession()->set('selected_trace_type', $type);

        if ($request->query->get('trace')) {
            $trace = $this->traceRepository->find($request->query->get('trace'));
            $trace->setContenu([]);
            $trace->setType($type);
            $this->traceRepository->save($trace, true);
        } else {
            $trace = new Trace();
        }

        $selectedTraceType = $request->getSession()->get('selected_trace_type');

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

        $typesTrace = $this->traceRegistry->getTypeTraces();

        return $this->render('partials/trace/_type_trace_form.html.twig', [
            'page' => $page,
            'typesTrace' => $typesTrace,
            'type' => $type,
            'trace' => $trace ?? new Trace(),
            'selectedTraceType' => $selectedTraceType,
            'formType' => $formType,
            'typeTrace' => $typeTrace,
        ]);
    }


    //todo: refactor
//    #[Route('/edit/{id}', name: 'app_portfolio_univ_edit')]
//    public function edit(Request $request, PortfolioUniv $portfolio, ?string $step, ?bool $edit): Response
//    {
//        $user = $this->getUser()->getEtudiant();
//
//        $step = $request->query->get('step', $step);
//
//        if ($step === null) {
//            $step = 'portfolio';
//        }
//
//        switch ($step) {
//            case 'portfolio':
//                $form = $this->createForm(PortfolioUnivType::class, $portfolio);
//                $form->handleRequest($request);
//                if ($form->isSubmitted() && $form->isValid()) {
//                    $portfolio->setLibelle($form->get('libelle')->getData());
//                    $portfolio->setDescription($form->get('description')->getData());
//                    $imageFile = $form['banniere']->getData();
//                    if ($imageFile) {
//                        $imageFileName = uniqid() . '.' . $imageFile->guessExtension();
//                        //Vérifier si le fichier est au bon format
//                        if (in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
//                            $imageFile->move($_ENV['PATH_FILES'], $imageFileName);
//                            $portfolio->setBanniere($_ENV['SRC_FILES'] . '/' . $imageFileName);
//                        } elseif (!in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
//                            $this->addFlash('danger', 'L\'image doit être au format jpg, jpeg, png, gif, svg ou webp');
//                        }
//                    } else {
//                        $portfolio->setBanniere($_ENV['SRC_FILES'] . '/banniere.jpg');
//                    }
//                    $portfolio->setVisibilite($form->get('visibilite')->getData());
//                    $portfolio->setOptSearch($form->get('optSearch')->getData());
//
//                    $this->portfolioUnivRepository->save($portfolio, true);
//                }
//                break;
//
//            case 'newPage':
//                $page = new Page();
//                $page->setPortfolio($portfolio);
//                $allPages = $this->pageRepository->findBy(['portfolio' => $portfolio]);
//                $page->setOrdre(count($allPages) + 1);
//                $page->setLibelle('Nouvelle page');
//                $this->pageRepository->save($page, true);
//
//                $form = $this->createForm(PageType::class, $page);
//
//                $step = 'page';
//                break;
//
//            case 'page':
//
//                $page = $this->pageRepository->find($request->query->get('page'));
//                $form = $this->createForm(PageType::class, $page);
//
//                $traces = $this->traceRepository->findNotInPage($page, $user->getBibliotheques());
//
//                $form->handleRequest($request);
//                if ($form->isSubmitted() && $form->isValid()) {
//                    $page->setLibelle($form->get('libelle')->getData());
//                    $page->setDescription($form->get('description')->getData());
//                    $this->pageRepository->save($page, true);
//
//                    $edit = false;
//                }
//
//                $tracesPage = $this->traceRepository->findInPage($page);
//
//                $edit = $request->query->get('edit', $edit);
//                $step = 'page';
//
//                break;
//
//            case 'addTrace':
//
//                $page = $this->pageRepository->find($request->query->get('page'));
//                $edit = false;
//
//                $datas = $request->request->all();
//
//                foreach ($datas as $data) {
//                    foreach ($data as $traceId) {
//                        $trace = $this->traceRepository->find($traceId);
//
//                        $tracePage = new TracePage();
//                        $tracePage->setPage($page);
//                        $tracePage->setTrace($trace);
//                        $tracePage->setOrdre(count($page->getTracePages()) + 1);
//
//                        $this->tracePageRepository->save($tracePage, true);
//                    }
//                }
//
//                $step = 'page';
//
//                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);
//
//            case 'upTrace' :
//                $trace = $this->traceRepository->find($request->query->get('trace'));
//                $page = $this->pageRepository->find($request->query->get('page'));
//                $tracePage = $this->tracePageRepository->findOneBy(['trace' => $trace, 'page' => $page]);
//
//                $ordre = $tracePage->getOrdre();
//                $previousTracePage = $this->tracePageRepository->findOneBy(['page' => $page, 'ordre' => $ordre - 1]);
//
//                $tracePage->setOrdre($ordre - 1);
//                $previousTracePage->setOrdre($ordre);
//
//                $this->tracePageRepository->save($tracePage, true);
//
//                $edit = false;
//                $step = 'page';
//
//                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);
//
//            case 'downTrace' :
//                $trace = $this->traceRepository->find($request->query->get('trace'));
//                $page = $this->pageRepository->find($request->query->get('page'));
//                $tracePage = $this->tracePageRepository->findOneBy(['trace' => $trace, 'page' => $page]);
//
//                $ordre = $tracePage->getOrdre();
//                $nextTracePage = $this->tracePageRepository->findOneBy(['page' => $page, 'ordre' => $ordre + 1]);
//
//                $tracePage->setOrdre($ordre + 1);
//                $nextTracePage->setOrdre($ordre);
//
//                $this->tracePageRepository->save($tracePage, true);
//
//                $edit = false;
//                $step = 'page';
//
//                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);
//
//            case 'deleteTrace' :
//                $trace = $this->traceRepository->find($request->query->get('trace'));
//                $page = $this->pageRepository->find($request->query->get('page'));
//                $tracePage = $this->tracePageRepository->findOneBy(['trace' => $trace, 'page' => $page]);
//
//                $this->tracePageRepository->delete($tracePage, true);
//
//                $edit = false;
//                $step = 'page';
//
//                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);
//
//            case 'editTrace' :
//                $page = $this->pageRepository->find($request->query->get('page'));
//                $trace = $this->traceRepository->find($request->query->get('trace'));
//
//                $typesTrace = $this->traceRegistry->getTypeTraces();
//                $user = $this->getUser();
//
//                $competences = $this->competencesService->getCompetences($user);
//
//                if (isset($competences['apcNiveaux'])) {
//                    $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcNiveaux']]);
//                } else {
//                    $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcApprentissagesCritiques']]);
//                }
//                // Vérifier si un type de trace a été passé en paramètre
//                $selectedTraceType = $request->query->get('type', null);
//                if ($selectedTraceType !== null) {
//                    $formType = $selectedTraceType::FORM;
//                    $formType = $this->createForm($formType, $trace);
//                    $formType = $formType->createView();
//                    $typeTrace = $selectedTraceType::TYPE;
//                } elseif ($trace->getType() !== null) {
//                    $selectedTraceType = $trace->getType();
//                    if ($selectedTraceType === 'image') {
//                        $selectedTraceType = $this->traceImage::CLASS_NAME;
//                    } elseif ($selectedTraceType === 'lien') {
//                        $selectedTraceType = $this->traceLien::CLASS_NAME;
//                    } elseif ($selectedTraceType === 'video') {
//                        $selectedTraceType = $this->traceVideo::CLASS_NAME;
//                    } elseif ($selectedTraceType === 'pdf') {
//                        $selectedTraceType = $this->tracePdf::CLASS_NAME;
//                    }
//                    $formType = $selectedTraceType::FORM;
//                    $formType = $this->createForm($formType, $trace);
//                    $formType = $formType->createView();
//                    $typeTrace = $selectedTraceType::TYPE;
//                } else {
//                    $formType = null;
//                }
//
//                break;
//
//            case 'showTrace':
//                $trace = $this->traceRepository->find($request->query->get('trace'));
//                $page = $this->pageRepository->find($request->query->get('page'));
//                $portfolio = $page->getPortfolio();
//                $form = $this->createForm(PortfolioUnivType::class, $portfolio);
//                break;
//
//            case 'newTrace' :
//                $page = $this->pageRepository->find($request->query->get('page'));
//
//                $typesTrace = $this->traceRegistry->getTypeTraces();
//                $user = $this->getUser();
//
//                $competences = $this->competencesService->getCompetences($user);
//
//                $trace = new Trace();
//
//                if (isset($competences['apcNiveaux'])) {
//                    $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcNiveaux']]);
//                } else {
//                    $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcApprentissageCritiques']]);
//                }
//                // Vérifier si un type de trace a été passé en paramètre
//                $selectedTraceType = $request->query->get('type', null);
//                if ($selectedTraceType !== null) {
//                    $formType = $selectedTraceType::FORM;
//                    $formType = $this->createForm($formType, $trace);
//                    $formType = $formType->createView();
//                    $typeTrace = $selectedTraceType::TYPE;
//                } else {
//                    $formType = null;
//                }
//
//                break;
//
//            case 'newType':
//                $type = $request->query->get('type');
//                $page = $this->pageRepository->find($request->query->get('page'));
//
//                // Stocker le type de trace dans la session
//                $request->getSession()->set('selected_trace_type', $type);
//
//                $step = 'newTrace';
//
//                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit, 'type' => $type]);
//
//            case 'editType':
//                $type = $request->query->get('type');
//                $page = $this->pageRepository->find($request->query->get('page'));
//                $trace = $this->traceRepository->find($request->query->get('trace'));
//                $trace->setContenu([]);
//                $this->traceRepository->save($trace, true);
//
//                // Stocker le type de trace dans la session
//                $request->getSession()->set('selected_trace_type', $type);
//
//                $step = 'editTrace';
//
//                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit, 'type' => $type, 'trace' => $trace->getId()]);
//
//            case 'saveTrace':
//                $data = $request->request->all();
//                $files = $request->files->all();
//                $formDatas = $request->request->all()['trace_abstract'];
//
//                $etudiant = $this->getUser()->getEtudiant();
//                $bibliotheque = $this->bibliothequeRepository->findOneBy(['etudiant' => $etudiant, 'actif' => true]);
//
//                $trace = new Trace();
//
//                if (isset($data['trace_lien'])) {
//                    $contenu = $data['trace_lien']['contenu'];
//                    $contenu = $this->traceLien->sauvegarde($contenu, null);
//                    $trace->setType($this->traceLien::TYPE);
//                } elseif (isset($files['trace_image'])) {
//                    $contenu = $files['trace_image']['contenu'];
//                    $contenu = $this->traceImage->sauvegarde($contenu, null);
//                    $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
//                        return is_string($item);
//                    });
//                    $trace->setType($this->traceImage::TYPE);
//                } elseif (isset($data['trace_video'])) {
//                    $contenu = $data['trace_video']['contenu'];
//                    $contenu = $this->traceVideo->sauvegarde($contenu, null);
//                    $trace->setType($this->traceVideo::TYPE);
//                } elseif (isset($files['trace_pdf'])) {
//                    $contenu = $files['trace_pdf']['contenu'];
//                    $contenu = $this->tracePdf->sauvegarde($contenu, null);
//                    $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
//                        return is_string($item);
//                    });
//                    $trace->setType($this->tracePdf::TYPE);
//                }
//
//                if (isset($contenu) && $contenu['success'] === false) {
//                    $this->addFlash('danger', $contenu['error']);
//                    return $this->redirectToRoute('app_trace_new');
//                } else {
//                    if (isset($contenu)) {
//                        $trace->setContenu($contenu['contenu']);
//                    }
//                    $trace->setBibliotheque($bibliotheque);
//                    $trace->setDateCreation(new \DateTime());
//                    $trace->setLibelle($formDatas['libelle']);
//                    $trace->setContexte($formDatas['contexte']);
//                    if (!empty($formDatas['dateRealisation'])) {
//                        $trace->setDateRealisation(\DateTime::createFromFormat('m-Y', $formDatas['dateRealisation']));
//                    } else {
//                        $trace->setDateRealisation(null);
//                    }
//                    $trace->setLegende($formDatas['legende']);
//                    $trace->setDescription($formDatas['description']);
//                    $this->traceRepository->save($trace, true);
//
//                    // récupérer les compétences stockés dans la session à la construction du formulaire
//                    $competences = $request->getSession()->get('competences');
//
//                    if (isset($request->request->all()['trace_abstract']['competences']) && !empty($request->request->all()['trace_abstract']['competences'])) {
//                        // récupérer les compétences qui ont été sélectionnées dans le formulaire
//                        $submittedCompetenceIds = $request->request->all()['trace_abstract']['competences'];
//
//                        $selectedCompetences = [];
//
//                        $competences = array_flip($competences);
//
//                        foreach ($submittedCompetenceIds as $id) {
//                            // recouper les compétences sélectionnées avec les compétences stockées dans la session pour récupérer les libellés et les id
//                            if (isset($competences[$id])) {
//                                $selectedCompetences[$id] = $competences[$id];
//                            }
//                        }
//
//                        $apcNiveaux = [];
//                        $apcApprentissageCritiques = [];
//
//
//                        foreach ($selectedCompetences as $id => $libelle) {
//                            // vérifier si un ApcNiveau existe avec l'id et le libellé
//                            $apcNiveau = $this->apcNiveauRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
//                            if ($apcNiveau) {
//                                $apcNiveaux[] = $apcNiveau;
//                                $validation = new Validation();
//                                $validation->setApcNiveau($apcNiveau);
//                                $validation->setTrace($trace);
//                                $validation->setEtat(0);
//                                $validation->setDateCreation(new \DateTime());
//                                $this->validationRepository->save($validation, true);
//                            } else {
//                                // vérifier si un ApcApprentissageCritique existe avec l'id et le libellé
//                                $apcApprentissageCritique = $this->apcApprentissageCritiqueRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
//                                if ($apcApprentissageCritique) {
//                                    $apcApprentissageCritiques[] = $apcApprentissageCritique;
//                                    $validation = new Validation();
//                                    $validation->setApcApprentissageCritique($apcApprentissageCritique);
//                                    $validation->setTrace($trace);
//                                    $validation->setEtat(0);
//                                    $validation->setDateCreation(new \DateTime());
//                                    $this->validationRepository->save($validation, true);
//                                }
//                            }
//                        }
//                    }
//                }
//
//                $page = $this->pageRepository->find($request->query->get('page'));
//                $tracePage = new TracePage();
//                $tracePage->setPage($page);
//                $tracePage->setTrace($trace);
//                $tracePage->setOrdre(count($page->getTracePages()) + 1);
//
//                $this->tracePageRepository->save($tracePage, true);
//
//                $step = 'page';
//                $edit = false;
//
//                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);
//
//            case 'saveEditTrace':
//                $data = $request->request->all();
//                $files = $request->files->all();
//                $formDatas = $request->request->all()['trace_abstract'];
//                $page = $this->pageRepository->find($request->query->get('page'));
//
//                $etudiant = $this->getUser()->getEtudiant();
//                $bibliotheque = $this->bibliothequeRepository->findOneBy(['etudiant' => $etudiant, 'actif' => true]);
//
//                $trace = $this->traceRepository->find($request->query->get('trace'));
//
//                if (isset($files['trace_image']) && !isset($data['img'])) {
//                    $contenu = $files['trace_image']['contenu'];
//                    $contenu = $this->traceImage->sauvegarde($contenu, null);
//                    $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
//                        return is_string($item);
//                    });
//                    $trace->setType($this->traceImage::TYPE);
//                } elseif (isset($data['img']) && !isset($files['trace_image'])) {
//                    $trace->setContenu($data['img']);
//                    $trace->setType($this->traceImage::TYPE);
//                } elseif (isset($data['img']) && isset($files['trace_image'])) {
//                    $contenu = $files['trace_image']['contenu'];
//                    $contenu = $this->traceImage->sauvegarde($contenu, null);
//                    $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
//                        return is_string($item);
//                    });
//                    $contenu['contenu'] = array_merge($contenu['contenu'], $data['img']);
//                    $trace->setType($this->traceImage::TYPE);
//                } elseif (isset($data['trace_lien'])) {
//                    $contenu = $data['trace_lien']['contenu'];
//                    // si une entrée est vide, on la supprime
//                    $contenu = array_filter($contenu, function ($item) {
//                        return $item !== '';
//                    });
//                    $contenu = $this->traceLien->sauvegarde($contenu, null);
//                    $trace->setType($this->traceLien::TYPE);
//                } elseif (isset($files['trace_pdf']) && !isset($data['pdf'])) {
//                    $contenu = $files['trace_pdf']['contenu'];
//                    $contenu = $this->tracePdf->sauvegarde($contenu, null);
//                    $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
//                        return is_string($item);
//                    });
//                    $trace->setType($this->tracePdf::TYPE);
//                } elseif (isset($data['pdf']) && !isset($files['trace_pdf'])) {
//                    $trace->setContenu($data['pdf']);
//                    $trace->setType($this->tracePdf::TYPE);
//                } elseif (isset($data['pdf']) && isset($files['trace_pdf'])) {
//                    $contenu = $files['trace_pdf']['contenu'];
//                    $contenu = $this->tracePdf->sauvegarde($contenu, null);
//                    $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
//                        return is_string($item);
//                    });
//                    $contenu['contenu'] = array_merge($contenu['contenu'], $data['pdf']);
//                    $trace->setType($this->tracePdf::TYPE);
//                } elseif (isset($data['trace_video'])) {
//                    $contenu = $data['trace_video']['contenu'];
//                    // si une entrée est vide, on la supprime
//                    $contenu = array_filter($contenu, function ($item) {
//                        return $item !== '';
//                    });
//                    $contenu = $this->traceVideo->sauvegarde($contenu, null);
//                    $trace->setType($this->traceVideo::TYPE);
//                } else {
//                    $trace->setContenu([]);
//                }
//
//                if (isset($contenu) && $contenu['success'] === false) {
//                    $this->addFlash('danger', $contenu['error']);
//                    return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => 'editTrace', 'page' => $page->getId(), 'trace' => $trace->getId()]);
//                } else {
//                    if (isset($contenu)) {
//                        $trace->setContenu($contenu['contenu']);
//                    }
//                    $trace->setBibliotheque($bibliotheque);
//                    $trace->setDateCreation(new \DateTime());
//                    $trace->setLibelle($formDatas['libelle']);
//                    $trace->setContexte($formDatas['contexte']);
//                    if (!empty($formDatas['dateRealisation'])) {
//                        $trace->setDateRealisation(\DateTime::createFromFormat('m-Y', $formDatas['dateRealisation']));
//                    } else {
//                        $trace->setDateRealisation(null);
//                    }
//                    $trace->setLegende($formDatas['legende']);
//                    $trace->setDescription($formDatas['description']);
//                    $this->traceRepository->save($trace, true);
//
//                    // récupérer les compétences stockés dans la session à la construction du formulaire
//                    $competences = $request->getSession()->get('competences');
//
//                    if (isset($request->request->all()['trace_abstract']['competences']) && !empty($request->request->all()['trace_abstract']['competences'])) {
//                        // récupérer les compétences qui ont été sélectionnées dans le formulaire
//                        $submittedCompetenceIds = $request->request->all()['trace_abstract']['competences'];
//
//                        // récupérer toutes les validations pour la trace actuelle
//                        $validations = $this->validationRepository->findBy(['trace' => $trace]);
//
//                        // parcourir chaque validation
//                        foreach ($validations as $validation) {
//                            // obtenir la compétence associée à la validation
//                            $competence = $validation->getApcNiveau() ?? $validation->getApcApprentissageCritique();
//
//                            // vérifier si la compétence a été déselectionnée
//                            if (!in_array($competence->getId(), $submittedCompetenceIds)) {
//                                // si la compétence a été déselectionnée, supprimer la validation
//                                $this->validationRepository->delete($validation, true);
//                            }
//                        }
//
//                        $selectedCompetences = [];
//
//                        $competences = array_flip($competences);
//
//                        foreach ($submittedCompetenceIds as $id) {
//                            // recouper les compétences sélectionnées avec les compétences stockées dans la session pour récupérer les libellés et les id
//                            if (isset($competences[$id])) {
//                                $selectedCompetences[$id] = $competences[$id];
//                            }
//                        }
//
//
//                        foreach ($selectedCompetences as $id => $libelle) {
//                            // vérifier si un ApcNiveau existe avec l'id et le libellé
//                            $apcNiveau = $this->apcNiveauRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
//                            if ($apcNiveau) {
//                                // vérifier si une validation avec cette compétence existe déjà pour cette trace
//                                $existingValidation = $this->validationRepository->findOneBy(['trace' => $trace, 'apcNiveau' => $apcNiveau]);
//                                if (!$existingValidation) {
//                                    // si aucune validation existante n'est trouvée, créez une nouvelle validation
//                                    $validation = new Validation();
//                                    $validation->setApcNiveau($apcNiveau);
//                                    $validation->setTrace($trace);
//                                    $validation->setEtat(0);
//                                    $validation->setDateCreation(new \DateTime());
//                                    $this->validationRepository->save($validation, true);
//                                }
//                            } else {
//                                // vérifier si un ApcApprentissageCritique existe avec l'id et le libellé
//                                $apcApprentissageCritique = $this->apcApprentissageCritiqueRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
//                                if ($apcApprentissageCritique) {
//                                    // vérifier si une validation avec cette compétence existe déjà pour cette trace
//                                    $existingValidation = $this->validationRepository->findOneBy(['trace' => $trace, 'apcApprentissageCritique' => $apcApprentissageCritique]);
//                                    if (!$existingValidation) {
//                                        // si aucune validation existante n'est trouvée, créez une nouvelle validation
//                                        $validation = new Validation();
//                                        $validation->setApcApprentissageCritique($apcApprentissageCritique);
//                                        $validation->setTrace($trace);
//                                        $validation->setEtat(0);
//                                        $validation->setDateCreation(new \DateTime());
//                                        $this->validationRepository->save($validation, true);
//                                    }
//                                }
//                            }
//                        }
//                    } else {
//                        $validations = $this->validationRepository->findBy(['trace' => $trace]);
//                        foreach ($validations as $validation) {
//                            $this->validationRepository->delete($validation, true);
//                        }
//                    }
//                }
//
//                $step = 'page';
//                $edit = false;
//
////                break;
//                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);
//
//
//        }
//
//        $pages = $portfolio->getPages();
//
//        return $this->render('portfolio_univ/edit.html.twig', [
//            'portfolio' => $portfolio,
//            'pages' => $pages,
//            'form' => $form->createView() ?? null,
//            'step' => $step,
//            'page' => $page ?? null,
//            'edit' => $edit ?? true,
//            'traces' => $traces ?? null,
//            'tracesPage' => $tracesPage ?? null,
//            'typesTrace' => $typesTrace ?? null,
//            'trace' => $trace ?? null,
//            'type' => $typeTrace ?? null,
//            'formType' => $formType ?? null,
//            'selectedTraceType' => $selectedTraceType ?? null,
//            'apcNiveaux' => $apcNiveaux ?? null,
//            'apcApprentissageCritiques' => $apcApprentissageCritiques ?? null,
//        ]);
//    }

    #[Route('/delete/{id}', name: 'app_portfolio_univ_delete')]
    public function delete(PortfolioUniv $portfolio): Response
    {
        $this->portfolioUnivRepository->remove($portfolio, true);

        return $this->redirectToRoute('app_biblio_portfolio_univ');
    }
}
