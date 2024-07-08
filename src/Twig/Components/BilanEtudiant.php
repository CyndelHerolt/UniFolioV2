<?php

namespace App\Twig\Components;

use App\Repository\AnneeUniversitaireRepository;
use App\Repository\EtudiantRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('BilanEtudiant')]
final class BilanEtudiant
{
    use DefaultActionTrait;

    public int $id;

    public function __construct(
        private readonly EtudiantRepository $etudiantRepository,
        private readonly PortfolioUnivRepository $portfolioUnivRepository,
        private readonly TraceRepository $traceRepository,
        private readonly AnneeUniversitaireRepository $anneeUniversitaireRepository,
    )
    {

    }

    public function getNbTraces()
    {
        $annee = $this->anneeUniversitaireRepository->findOneBy(['active' => true]);
        $portfolio = $this->portfolioUnivRepository->findBy(['etudiant' => $this->id, 'anneeUniv' => $annee]);
        $traces = $this->traceRepository->findByPortfolio($portfolio);

        return count($traces);
    }

    public function getValidation()
    {

    }

    public function getEtudiant()
    {
        return $this->etudiantRepository->find($this->id);
    }
}
