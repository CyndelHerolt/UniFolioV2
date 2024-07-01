<?php

namespace App\Twig\Components;

use App\Entity\PortfolioUniv;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\PortfolioUnivRepository;
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
        private readonly Security $security,
        private readonly PortfolioUnivRepository $portfolioUnivRepository,
        private readonly AnneeUniversitaireRepository $anneeUniversitaireRepository,
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
    public function deleteSelectedPortfolios()
    {
        if (empty($this->selectedPortfoliosUniv)) {
            return;
        }
        foreach ($this->selectedPortfoliosUniv as $portfolioId) {
            $portfolio = $this->portfolioUnivRepository->find($portfolioId);
            $this->portfolioUnivRepository->remove($portfolio, true);
        }
        $this->allPortfolios = $this->getAllPortfolios();
    }



    public function getAllPortfolios()
    {
        return $this->portfolioUnivRepository->findBy(['etudiant' => $this->security->getUser()->getEtudiant()], ['date_creation' => 'DESC']);
    }
}
