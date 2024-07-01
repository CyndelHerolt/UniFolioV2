<?php

namespace App\Twig\Components;

use App\Entity\PortfolioUniv;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceCompetenceRepository;
use App\Repository\TracePageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('AllPortfolios')]
final class AllPortfolios
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    /** @var PortfolioUniv[] */
    public array $allPortfolios = [];

    #[LiveProp(writable: true)]
    public array $selectedPortfoliosUniv = [];

    public function __construct(
        private readonly Security                     $security,
        private readonly PortfolioUnivRepository      $portfolioUnivRepository,
        private readonly AnneeUniversitaireRepository $anneeUniversitaireRepository,
        private readonly TracePageRepository          $tracePageRepository,
        private readonly TraceCompetenceRepository    $traceCompetenceRepository,
    )
    {
        $this->allPortfolios = $this->getAllPortfolios();
    }

    #[LiveAction]
    public function selectAll()
    {
        // si tous les portfolios sont déjà sélectionnés, on les déselectionne tous
        if (count($this->selectedPortfoliosUniv) === count($this->allPortfolios)) {
            $this->selectedPortfoliosUniv = [];
        } else {
            $this->selectedPortfoliosUniv = array_map(fn(PortfolioUniv $portfolio) => $portfolio->getId(), $this->allPortfolios);
        }
    }

    #[LiveAction]
    public function resetSelectedPortfolios()
    {
        if (empty($this->selectedPortfoliosUniv)) {
            return;
        }
        foreach ($this->selectedPortfoliosUniv as $portfolioId) {
            $portfolio = $this->portfolioUnivRepository->find($portfolioId);
            $pages = $portfolio->getPages();
            // on supprime toutes les tracesPages de chaque page du portfolio
            foreach ($pages as $page) {
                $tracePages = $page->getTracePages();
                foreach ($tracePages as $tracePage) {
                    $traces = $tracePage->getTrace();
                    foreach ($traces as $trace) {
                        $traceCompetences = $trace->getTraceCompetences();
                        foreach ($traceCompetences as $traceCompetence) {
                            $this->traceCompetenceRepository->remove($traceCompetence, true);
                        }
                    }
                    $this->tracePageRepository->delete($tracePage, true);
                }
            }

        }
        $this->allPortfolios = $this->getAllPortfolios();
    }

    public function getAnneeUniv()
    {
        return $this->anneeUniversitaireRepository->findOneBy(['active' => true]);
    }

    public function testPortfolioAnneeUnivExists()
    {
        $anneeUniv = $this->getAnneeUniv();
        // si il existe un portfolio de l'étudiant qui a pour anneeUniversitaire l'année universitaire active retourner true sinon false
        return $this->portfolioUnivRepository->findOneBy(['etudiant' => $this->security->getUser()->getEtudiant(), 'anneeUniv' => $anneeUniv]) !== null;
    }

    public function getAllPortfolios()
    {
        // trier les resutlats par date de création
        return $this->portfolioUnivRepository->findBy(['etudiant' => $this->security->getUser()->getEtudiant()], ['date_creation' => 'DESC']);
    }
}
