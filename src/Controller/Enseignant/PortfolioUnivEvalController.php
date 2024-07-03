<?php

namespace App\Controller\Enseignant;

use App\Entity\PortfolioUniv;
use App\Form\CritereType;
use App\Repository\CritereApprentissageCritiqueRepository;
use App\Repository\CritereNiveauRepository;
use App\Repository\PageRepository;
use App\Repository\TraceRepository;
use App\Service\CompetencesService;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enseignant/portfolioUniv')]
class PortfolioUnivEvalController extends AbstractController
{
    public function __construct(
        private readonly PageRepository                         $pageRepository,
        private readonly TraceRepository                        $traceRepository,
        private readonly CompetencesService                     $competencesService,
        private readonly CritereNiveauRepository                $critereNiveauRepository,
        private readonly CritereApprentissageCritiqueRepository $critereApprentissageCritiqueRepository,
    )
    {

    }

    #[Route('/show/{id}', name: 'app_portfolio_univ_eval_show', defaults: ["page" => 1])]
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

        $competences = $this->competencesService->getCompetencesPortfolio($portfolio);

        if ($competences['apcNiveaux']) {
            $criteresCompetences = $this->critereNiveauRepository->findByPage($currentPage->getId());
        } else {
            $criteresCompetences = $this->critereApprentissageCritiqueRepository->findByPage($currentPage->getId());
        }

        $edit = $request->query->get('edit', false);

        if ($edit) {
            $critereCompetence = $request->query->get('critereCompetence');
            return $this->render('partials/_critere_eval_form.html.twig', [
                'editCritereId' => $critereCompetence,
                'critereCompetence' => $this->critereNiveauRepository->findOneBy(['id' => $critereCompetence]),
                'portfolio' => $portfolio,
                'criteresCompetences' => $criteresCompetences,
            ]);
        }

        // si la requête contient un paramètre "valeur" et un paramètre "critereCompetenceId"
        if ($request->query->get('valeur') && $request->query->get('critereCompetenceId')) {
            $datas = $request->query->get('valeur');

            $valeur = explode(' :', $datas)[0];
            $libelle = explode(' : ', $datas)[1];

            $critereCompetenceId = $request->query->get('critereCompetenceId');

            if ($competences['apcNiveaux']) {
                $critereCompetence = $this->critereNiveauRepository->findOneBy(['id' => $critereCompetenceId]);
                $critereCompetence->setValeur($valeur);
                $critereCompetence->setLibelle($libelle);
                $this->critereNiveauRepository->save($critereCompetence, true);
            } else {
                $critereCompetence = $this->critereApprentissageCritiqueRepository->findOneBy(['id' => $critereCompetenceId]);
                $critereCompetence->setValeur($valeur);
                $critereCompetence->setLibelle($libelle);
                $this->critereApprentissageCritiqueRepository->save($critereCompetence, true);
            }

            return $this->redirectToRoute('app_portfolio_univ_eval_show', ['id' => $portfolio->getId()]);
        }


        return $this->render('portfolio_univ_eval/show_eval.html.twig', [
            'portfolio' => $portfolio,
            'pages' => $pagerfanta,
            'tracesPage' => $tracesPage,
            'apcNiveaux' => $competences['apcNiveaux'] ?? null,
            'apcApprentissageCritiques' => $competences['apcApprentissagesCritiques'] ?? null,
            'groupedApprentissageCritiques' => $competences['groupedApprentissagesCritiques'] ?? null,
            'criteresCompetences' => $criteresCompetences,
            'edit' => $edit,
            'editCritereId' => $editCritereId ?? null,
        ]);
    }
}
