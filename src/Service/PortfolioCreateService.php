<?php

namespace App\Service;

use App\Components\Trace\TraceRegistry;
use App\Controller\BaseController;
use App\Entity\ApcNiveau;
use App\Entity\CritereApprentissageCritique;
use App\Entity\CritereNiveau;
use App\Entity\Page;
use App\Entity\PortfolioUniv;
use App\Entity\Trace;
use App\Entity\TraceCompetence;
use App\Entity\TracePage;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\CritereApprentissageCritiqueRepository;
use App\Repository\CritereNiveauRepository;
use App\Repository\CriteresRepository;
use App\Repository\DepartementRepository;
use App\Repository\PageRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceCompetenceRepository;
use App\Repository\TracePageRepository;
use App\Repository\TraceRepository;
use App\Repository\ValidationRepository;
use Symfony\Component\HttpFoundation\Request;

class PortfolioCreateService extends BaseController
{
    public function __construct(
        private readonly PortfolioUnivRepository            $portfolioUnivRepository,
        private readonly PageRepository                     $pageRepository,
        private readonly DataUserSessionService             $dataUserSessionService,
        private readonly CompetencesService                 $competencesService,
        private readonly DepartementRepository              $departementRepository,
        private readonly CritereNiveauRepository            $critereNiveauRepository,
        private readonly CritereApprentissageCritiqueRepository $critereApprentissageCritiqueRepository,
        private readonly AnneeUniversitaireRepository       $anneeUniversitaireRepository,
        private readonly CriteresRepository                 $criteresRepository,
    )
    {
        parent::__construct(
            $this->dataUserSessionService,
        );
    }


    public function create($etudiant)
    {
        $semestre = $etudiant->getSemestre();
        $user = $etudiant->getUser();

        $portfolio = new PortfolioUniv();
        $portfolio->setEtudiant($etudiant);
        $portfolio->setAnnee($semestre->getAnnee());
        $portfolio->setAnneeUniv($this->anneeUniversitaireRepository->findOneBy(['active' => true]));
        $portfolio->setLibelle('Portfolio de ' . $etudiant->getNom() . ' ' . $etudiant->getPrenom());
        $portfolio->setBanniere('/files_directory/banniere.jpg');
        $portfolio->setDescription('Modifier la description de votre portfolio');
        $portfolio->setDateCreation(new \DateTime());
        $portfolio->setDateModification(new \DateTime());

        $competences = $this->competencesService->getCompetencesEtudiant($user);

//        dd($competences);
//        $competences = $competences['apcNiveaux'] ?? $competences['apcApprentissagesCritiques'];

        if (!empty($competences['apcNiveaux'])) {
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

        $departement = $this->departementRepository->findDepartementEtudiant($etudiant);
        $criteres = $this->criteresRepository->findByDepartement($departement->getId());

        $pages = $this->pageRepository->findBy(['portfolio' => $portfolio]);

        foreach ($pages as $page) {
            $competence = $page->getApcNiveau() ?? $page->getApcApprentissageCritique();
            foreach ($criteres as $critere) {
                if ($competence instanceof ApcNiveau) {
                    $eval = new CritereNiveau();
                    $eval->setCritere($critere);
                    $eval->setPage($page);
                    $eval->setApcNiveau($competence);
                    $eval->setValeur(null);
                    $this->critereNiveauRepository->save($eval, true);
                } else {
                    $eval = new CritereApprentissageCritique();
                    $eval->setCritere($critere);
                    $eval->setPage($page);
                    $eval->setApprentissageCritique($competence);
                    $eval->setValeur(null);
                    $this->critereApprentissageCritiqueRepository->save($eval, true);
                }
            }
        }
    }
}