<?php

namespace App\Controller\Etudiant;

use App\Components\Trace\TraceRegistry;
use App\Controller\BaseController;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\PortfolioPersoRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceRepository;
use App\Service\DataUserSessionService;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant')]
class DashboardEtudiantController extends BaseController
{
    public function __construct(
        private readonly Security                 $security,
        private readonly BibliothequeRepository   $bibliothequeRepository,
        private readonly TraceRepository          $traceRepository,
        private readonly PortfolioUnivRepository  $portfolioUnivRepository,
        private readonly PortfolioPersoRepository $portfolioPersoRepository,
        private readonly TraceRegistry            $traceRegistry,
        private readonly AnneeUniversitaireRepository $anneeUniversitaireRepository,
        private readonly DataUserSessionService   $dataUserSessionService,
    )
    {
        parent::__construct(
            $this->dataUserSessionService,
        );
    }

    #[Route('/dashboard', name: 'app_dashboard_etudiant')]
    public function index(Request $request): Response
    {
        // Vérifier que l'utilisateur est connecté sinon on le redirige vers la page d'erreur
        if (!$this->security->getUser()) {
            return $this->render('security/accessDenied.html.twig');
        }
        
        // Récupérer la bibliothèque de l'utilisateur connecté
        $etudiant = $this->security->getUser()->getEtudiant();
        $biblio = $this->bibliothequeRepository->findOneBy(['etudiant' => $etudiant]);

        // Récupérer les traces de la bibliothèque
        $traces = $this->traceRepository->findBy(['bibliotheque' => $biblio]);

        $portfoliosUniv = $this->portfolioUnivRepository->findBy(['etudiant' => $etudiant]);
        $portfoliosPerso = $this->portfolioPersoRepository->findBy(['etudiant' => $etudiant]);
        $portfolios = array_merge($portfoliosUniv, $portfoliosPerso);

        $annee = $this->anneeUniversitaireRepository->findOneBy(['active' => true]);

        $portfolioCurrent = $this->portfolioUnivRepository->findOneBy(['etudiant' => $this->security->getUser()->getEtudiant(), 'anneeUniv' => $annee]);


        // Créer un adaptateur pour Pagerfanta en utilisant le tableau des portfolios
        $adapter = new ArrayAdapter($portfolios);

        // Créer une instance de Pagerfanta avec l'adaptateur
        $pagerfanta = new Pagerfanta($adapter);

        // Définir le nombre maximum d'éléments par page
        $pagerfanta->setMaxPerPage(4);

        // Définir la page actuelle
        $pagerfanta->setCurrentPage($request->query->get('page', 1));


        // Récupérer évals et commentaires
        $retourPedagogiques = [];
        foreach ($traces as $trace) {
            foreach ($trace->getCommentaires() as $commentaire) {
                // si le commentaire est visible on l'ajoute
                if ($commentaire->isVisibilite()) {
                    $retourPedagogiques[] = $commentaire;
                }
            }
        }
        // On trie par date de création
        usort($retourPedagogiques, function ($a, $b) {
            return $b->getDateCreation() <=> $a->getDateCreation();
        });
        usort($traces, function ($a, $b) {
            return $b->getDateCreation() <=> $a->getDateCreation();
        });
        usort($portfolios, function ($a, $b) {
            return $b->getDateCreation() <=> $a->getDateCreation();
        });

        // On ne garde que les 4 premiers
        $retourPedagogiques = array_slice($retourPedagogiques, 0, 4);
        $traces = array_slice($traces, 0, 4);
        $portfolios = array_slice($portfolios, 0, 4);

        $typesTrace = $this->traceRegistry->getTypeTraces();

        return $this->render('dashboard_etudiant/index.html.twig', [
            'retourPedagogiques' => $retourPedagogiques,
            'portfolioCurrent' => $portfolioCurrent,
            'traces' => $traces,
            'portfolios' => $pagerfanta,
            'typesTrace' => $typesTrace,
        ]);
    }
}
