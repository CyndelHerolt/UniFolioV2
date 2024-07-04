<?php

namespace App\Twig\Components;

use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\PortfolioUnivRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PortfolioUnivBiblioCard
{
    public int $id;

    public ?array $competences = null;

    public function __construct(
        private readonly PortfolioUnivRepository $portfolioUnivRepository,
        private readonly ApcNiveauRepository $apcNiveauRepository,
        private readonly ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
    )
    {
    }

    public function getPortfolioUniv()
    {
        $portfolioUniv = $this->portfolioUnivRepository->find($this->id);

        $etudiant = $portfolioUniv->getEtudiant();
        $departement = $etudiant->getSemestre()->getAnnee()->getDiplome()->getDepartement();

        if ($departement->getOptCompetence() === 1) {
            $this->competences = $this->apcNiveauRepository->findByPortfolioUniversitaire($portfolioUniv->getId());
        } else {
            $this->competences = $this->apcApprentissageCritiqueRepository->findByPortfolioUniversitaire($portfolioUniv->getId());
        }

        return $portfolioUniv;
    }
}
