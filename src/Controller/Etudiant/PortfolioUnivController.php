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
use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcNiveau;
use App\Entity\Page;
use App\Entity\PortfolioUniv;
use App\Entity\Trace;
use App\Entity\TraceCompetence;
use App\Entity\TracePage;
use App\Form\PageType;
use App\Form\PortfolioUnivType;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\PageRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceCompetenceRepository;
use App\Repository\TracePageRepository;
use App\Repository\TraceRepository;
use App\Service\CompetencesService;
use App\Service\DataUserSessionService;
use App\Service\TraceSaveService;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant/portfolio/univ')]
class PortfolioUnivController extends BaseController
{
    public function __construct(
        private readonly PortfolioUnivRepository            $portfolioUnivRepository,
        private readonly PageRepository                     $pageRepository,
        private readonly TraceRepository                    $traceRepository,
        private readonly TracePageRepository                $tracePageRepository,
        private readonly TraceRegistry                      $traceRegistry,
        private readonly TraceCompetenceRepository          $traceCompetenceRepository,
        private readonly DataUserSessionService             $dataUserSessionService,
        private readonly CompetencesService          $competencesService,
        private readonly TraceSaveService            $TraceSaveService
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

        $competences = $this->competencesService->getCompetencesEtudiant($user);

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
            $portfolio->setDateModification(new \DateTime('now'));
            $portfolio->setOptSearch($form->get('optSearch')->getData());

            $competences = $this->competencesService->getCompetencesEtudiant($this->getUser());

            if (isset($competences['apcNiveaux'])) {
                $competences = $competences['apcNiveaux'];
            } else {
                $competences = $competences['apcApprentissagesCritiques'];
            }
            $this->portfolioUnivRepository->save($portfolio, true);

            foreach ($competences as $competence) {
                $page = new Page();
                $page->setPortfolio($portfolio);
                $page->setLibelle($competence->getLibelle());
                if ($competence instanceof ApcNiveau) {
                    $page->setApcNiveau($competence);
                } else {
                    $page->setApcApprentissageCritique($competence);
                }

                $this->pageRepository->save($page, true);
            }


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
            $portfolio->setDateModification(new \DateTime('now'));
            $portfolio->setOptSearch($form->get('optSearch')->getData());

            $this->portfolioUnivRepository->save($portfolio, true);
        }

        return $this->render('portfolio_univ/edit.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form->createView(),
            'step' => 'portfolio'
        ]);
    }

    #[Route('/page/{id}', name: 'app_portfolio_univ_show_page')]
    public function showPortfolioPage(Request $request, ?int $id): Response
    {
        $user = $this->getUser()->getEtudiant();
        $page = $this->pageRepository->find($id);
        $portfolio = $page->getPortfolio();
        $traces = $this->traceRepository->findNotInPage($page, $user->getBibliotheques());
        $tracesPage = $this->traceRepository->findInPage($page);

        return $this->render('portfolio_univ/edit.html.twig', [
            'page' => $page,
            'step' => 'page',
            'portfolio' => $portfolio,
            'traces' => $traces,
            'tracesPage' => $tracesPage
        ]);
    }

    #[Route('/edit/page/{id}/new/trace', name: 'app_portfolio_univ_edit_new_trace')]
    public function editPortfolioNewTrace(Request $request, ?int $id): Response
    {
        $page = $this->pageRepository->find($id);
        $portfolio = $page->getPortfolio();
        $edit = false;

        $typesTrace = $this->traceRegistry->getTypeTraces();
        $user = $this->getUser();

        $competences = $this->competencesService->getCompetencesEtudiant($user);

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


        return $this->render('portfolio_univ/edit.html.twig', [
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
            'step' => 'newTrace',
            'portfolio' => $portfolio
        ]);
    }

    #[Route('/edit/page/{id}/edit/trace/{trace}', name: 'app_portfolio_univ_edit_trace')]
    public function editPortfolioTrace(Request $request, ?int $id, ?int $trace): Response
    {
        $page = $this->pageRepository->find($id);
        $portfolio = $page->getPortfolio();
        $trace = $this->traceRepository->find($trace);
        $edit = true;

        $typesTrace = $this->traceRegistry->getTypeTraces();
        $user = $this->getUser();

        $competences = $this->competencesService->getCompetencesEtudiant($user);

        if (isset($competences['apcNiveaux'])) {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcNiveaux']]);
        } else {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcApprentissagesCritiques']]);
        }

        $selectedTraceType = $request->getSession()->get('selected_trace_type', null);

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

        return $this->render('portfolio_univ/edit.html.twig', [
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
            'step' => 'newTrace',
            'portfolio' => $portfolio
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
        } elseif ($trace->getType() !== null) {
            $selectedTraceType = $trace->getType();
            $formType = $this->traceRegistry->getTypeTrace($selectedTraceType)::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
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
        ]);
    }

    #[Route('/edit/page/{id}/add/trace', name: 'app_portfolio_univ_edit_add_trace')]
    public function editPortfolioAddTrace(Request $request, ?int $id): Response
    {
        $page = $this->pageRepository->find($id);
        $competence = $page->getApcNiveau() ?? $page->getApcApprentissageCritique();

        if (isset($request->request->all()['traces'])) {
            $traces = $request->request->all()['traces'];

            foreach ($traces as $traceId) {
                $trace = $this->traceRepository->find($traceId);

                $traceCompetence = new TraceCompetence();
                $traceCompetence->setTrace($trace);
                $traceCompetence->setPortfolio($page->getPortfolio());
                if ($competence instanceof ApcNiveau) {
                    $traceCompetence->setApcNiveau($competence);
                } else {
                    $traceCompetence->setApcApprentissageCritique($competence);
                }
                $this->traceCompetenceRepository->save($traceCompetence, true);

                $tracePage = new TracePage();
                $tracePage->setPage($page);
                $tracePage->setTrace($trace);
                $tracePage->setOrdre(count($page->getTracePages()) + 1);

                $this->tracePageRepository->save($tracePage, true);
            }
        }

        return $this->redirectToRoute('app_portfolio_univ_show_page', ['id' => $page->getId()]);
    }

    #[Route('/edit/page/{id}/show/trace/{trace}', name: 'app_portfolio_univ_edit_show_trace')]
    public function editPortfolioShowTrace(Request $request, ?int $id, ?int $trace): Response
    {
        $page = $this->pageRepository->find($id);
        $portfolio = $page->getPortfolio();
        $trace = $this->traceRepository->find($trace);

        return $this->render('portfolio_univ/edit.html.twig', [
            'page' => $page,
            'portfolio' => $portfolio,
            'trace' => $trace,
            'step' => 'showTrace'
        ]);
    }

    #[Route('/edit/page/{id}/save/trace', name: 'app_portfolio_univ_edit_save_trace')]
    public function editPortfolioSaveTrace(Request $request, ?int $id): Response
    {
        $page = $this->pageRepository->find($id);
        if ($request->query->get('trace') !== null) {
            $trace = $this->traceRepository->find($request->query->get('trace'));
            $this->TraceSaveService->save($trace, $request);
        } else {
            $trace = new Trace();
            $this->TraceSaveService->save($trace, $request);

            $competence = $page->getApcNiveau() ?? $page->getApcApprentissageCritique();
            $traceCompetence = new TraceCompetence();
            $traceCompetence->setTrace($trace);
            $traceCompetence->setPortfolio($page->getPortfolio());
            if ($competence instanceof ApcNiveau) {
                $traceCompetence->setApcNiveau($competence);
            } else {
                $traceCompetence->setApcApprentissageCritique($competence);
            }
            $this->traceCompetenceRepository->save($traceCompetence, true);
        }

        // lier la trace à la page
        $tracePage = new TracePage();
        $tracePage->setPage($page);
        $tracePage->setTrace($trace);
        $tracePage->setOrdre(count($page->getTracePages()) + 1);

        $this->tracePageRepository->save($tracePage, true);

        return $this->redirectToRoute('app_portfolio_univ_show_page', ['id' => $page->getId()]);
    }

    #[Route('/edit/page/{id}/up/trace/{trace}', name: 'app_portfolio_univ_edit_up_trace')]
    public function editPortfolioUpTrace(?int $id, ?int $trace): Response
    {
        $page = $this->pageRepository->find($id);
        $tracePage = $this->tracePageRepository->findOneBy(['page' => $page, 'trace' => $trace]);
        $ordre = $tracePage->getOrdre();
        $tracePage->setOrdre($ordre - 1);

        // on récupère le tracePage qui est au dessus
        $tracePageUp = $this->tracePageRepository->findOneBy(['page' => $page, 'ordre' => $ordre - 1]);
        $tracePageUp->setOrdre($ordre);

        $this->tracePageRepository->save($tracePageUp, true);

        $this->tracePageRepository->save($tracePage, true);

        return $this->redirectToRoute('app_portfolio_univ_show_page', ['id' => $page->getId()]);
    }

    #[Route('/edit/page/{id}/down/trace/{trace}', name: 'app_portfolio_univ_edit_down_trace')]
    public function editPortfolioDownTrace(?int $id, ?int $trace): Response
    {
        $page = $this->pageRepository->find($id);
        $tracePage = $this->tracePageRepository->findOneBy(['page' => $page, 'trace' => $trace]);
        $ordre = $tracePage->getOrdre();
        $tracePage->setOrdre($ordre + 1);

        // on récupère le tracePage qui est en dessous
        $tracePageDown = $this->tracePageRepository->findOneBy(['page' => $page, 'ordre' => $ordre + 1]);
        $tracePageDown->setOrdre($ordre);

        $this->tracePageRepository->save($tracePageDown, true);

        $this->tracePageRepository->save($tracePage, true);

        return $this->redirectToRoute('app_portfolio_univ_show_page', ['id' => $page->getId()]);
    }

    #[Route('/edit/page/{id}/delete/trace/{trace}', name: 'app_portfolio_univ_edit_delete_trace')]
    public function editPortfolioDeleteTrace(?int $id, ?int $trace): Response
    {
        $page = $this->pageRepository->find($id);
        $tracePage = $this->tracePageRepository->findOneBy(['page' => $page, 'trace' => $trace]);
        $this->tracePageRepository->delete($tracePage, true);

        // retirer de la trace le lien avec la compétence identique à celle de la page
        $competence = $page->getApcNiveau() ?? $page->getApcApprentissageCritique();
        if ($competence instanceof ApcNiveau) {
            $traceCompetence = $this->traceCompetenceRepository->findOneBy(['trace' => $trace, 'apcNiveau' => $competence]);
        } elseif ($competence instanceof ApcApprentissageCritique) {
            $traceCompetence = $this->traceCompetenceRepository->findOneBy(['trace' => $trace, 'apcApprentissageCritique' => $competence]);
        }
        $this->traceCompetenceRepository->remove($traceCompetence, true);

        // gérer l'ordre des traces restantes
        $tracePages = $this->tracePageRepository->findBy(['page' => $page]);
        $ordre = 1;
        foreach ($tracePages as $tracePage) {
            $tracePage->setOrdre($ordre);
            $this->tracePageRepository->save($tracePage, true);
            $ordre++;
        }

        return $this->redirectToRoute('app_portfolio_univ_show_page', ['id' => $page->getId()]);
    }

    #[Route('/delete/{id}', name: 'app_portfolio_univ_delete')]
    public function delete(PortfolioUniv $portfolio): Response
    {
        $this->portfolioUnivRepository->remove($portfolio, true);

        return $this->redirectToRoute('app_biblio_portfolio_univ');
    }
}
