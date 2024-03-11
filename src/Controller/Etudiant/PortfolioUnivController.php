<?php

namespace App\Controller\Etudiant;

use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\TraceRegistry;
use App\Components\Trace\TypeTrace\TraceImage;
use App\Components\Trace\TypeTrace\TraceLien;
use App\Components\Trace\TypeTrace\TracePdf;
use App\Components\Trace\TypeTrace\TraceVideo;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant/portfolio/univ')]
class PortfolioUnivController extends AbstractController
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
        private readonly TraceLien                   $traceLien,
        private readonly TraceImage                  $traceImage,
        private readonly TracePdf                    $tracePdf,
        private readonly TraceVideo                  $traceVideo,
        private readonly ValidationRepository        $validationRepository,
        private readonly BibliothequeRepository      $bibliothequeRepository,
    )
    {
    }

    #[Route('/', name: 'app_portfolio_univ')]
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

            return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId()]);
        }

        return $this->render('portfolio_univ/form.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_portfolio_univ_edit')]
    public function edit(Request $request, PortfolioUniv $portfolio, ?string $step, ?bool $edit): Response
    {
        $user = $this->getUser()->getEtudiant();

        $step = $request->query->get('step', $step);

        if ($step === null) {
            $step = 'portfolio';
        }

        switch ($step) {
            case 'portfolio':
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
                break;

            case 'newPage':
                $page = new Page();
                $page->setPortfolio($portfolio);
                $allPages = $this->pageRepository->findBy(['portfolio' => $portfolio]);
                $page->setOrdre(count($allPages) + 1);
                $page->setLibelle('Nouvelle page');
                $this->pageRepository->save($page, true);

                $form = $this->createForm(PageType::class, $page);

                $step = 'page';
                break;

            case 'page':

                $page = $this->pageRepository->find($request->query->get('page'));
                $form = $this->createForm(PageType::class, $page);

                $traces = $this->traceRepository->findNotInPage($page, $user->getBibliotheques());

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $page->setLibelle($form->get('libelle')->getData());
                    $page->setDescription($form->get('description')->getData());
                    $this->pageRepository->save($page, true);

                    $edit = false;
                }

                $tracesPage = $this->traceRepository->findInPage($page);

                $edit = $request->query->get('edit', $edit);
                $step = 'page';

                break;

            case 'addTrace':

                $page = $this->pageRepository->find($request->query->get('page'));
                $edit = false;

                $datas = $request->request->all();

                foreach ($datas as $data) {
                    foreach ($data as $traceId) {
                        $trace = $this->traceRepository->find($traceId);

                        $tracePage = new TracePage();
                        $tracePage->setPage($page);
                        $tracePage->setTrace($trace);
                        $tracePage->setOrdre(count($page->getTracePages()) + 1);

                        $this->tracePageRepository->save($tracePage, true);
                    }
                }

                $step = 'page';

                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);

            case 'upTrace' :
                $trace = $this->traceRepository->find($request->query->get('trace'));
                $page = $this->pageRepository->find($request->query->get('page'));
                $tracePage = $this->tracePageRepository->findOneBy(['trace' => $trace, 'page' => $page]);

                $ordre = $tracePage->getOrdre();
                $previousTracePage = $this->tracePageRepository->findOneBy(['page' => $page, 'ordre' => $ordre - 1]);

                $tracePage->setOrdre($ordre - 1);
                $previousTracePage->setOrdre($ordre);

                $this->tracePageRepository->save($tracePage, true);

                $edit = false;
                $step = 'page';

                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);

            case 'downTrace' :
                $trace = $this->traceRepository->find($request->query->get('trace'));
                $page = $this->pageRepository->find($request->query->get('page'));
                $tracePage = $this->tracePageRepository->findOneBy(['trace' => $trace, 'page' => $page]);

                $ordre = $tracePage->getOrdre();
                $nextTracePage = $this->tracePageRepository->findOneBy(['page' => $page, 'ordre' => $ordre + 1]);

                $tracePage->setOrdre($ordre + 1);
                $nextTracePage->setOrdre($ordre);

                $this->tracePageRepository->save($tracePage, true);

                $edit = false;
                $step = 'page';

                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);

            case 'deleteTrace' :
                break;

            case 'editTrace' :
                break;

            case 'newTrace' :
                $page = $this->pageRepository->find($request->query->get('page'));

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
                // Vérifier si un type de trace a été passé en paramètre
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

                break;

            case 'newType':
                $type = $request->query->get('type');
                $page = $this->pageRepository->find($request->query->get('page'));

                // Stocker le type de trace dans la session
                $request->getSession()->set('selected_trace_type', $type);

                $step = 'newTrace';

                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit, 'type' => $type]);

            case 'saveTrace':
                $data = $request->request->all();
                $files = $request->files->all();
                dump($files);

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
                    dump('type inconnnu');
                }

                if ($contenu['success'] === false) {
                    dump('sauvegarde impossible');
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

                $page = $this->pageRepository->find($request->query->get('page'));
                $tracePage = new TracePage();
                $tracePage->setPage($page);
                $tracePage->setTrace($trace);
                $tracePage->setOrdre(count($page->getTracePages()) + 1);

                $this->tracePageRepository->save($tracePage, true);

                $step = 'page';
                $edit = false;

                return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId(), 'step' => $step, 'page' => $page->getId(), 'edit' => $edit]);
        }

        $pages = $portfolio->getPages();

        return $this->render('portfolio_univ/edit.html.twig', [
            'portfolio' => $portfolio,
            'pages' => $pages,
            'form' => $form->createView() ?? null,
            'step' => $step,
            'page' => $page ?? null,
            'edit' => $edit ?? true,
            'traces' => $traces ?? null,
            'tracesPage' => $tracesPage ?? null,
            'typesTrace' => $typesTrace ?? null,
            'trace' => $trace ?? null,
            'type' => $typeTrace ?? null,
            'formType' => $formType ?? null,
            'selectedTraceType' => $selectedTraceType ?? null,
            'apcNiveaux' => $apcNiveaux ?? null,
            'apcApprentissageCritiques' => $apcApprentissageCritiques ?? null,
        ]);
    }
}
