<?php

namespace App\Entity;

use App\Repository\TraceCompetenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TraceCompetenceRepository::class)]
class TraceCompetence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'traceCompetences')]
    private ?Trace $trace = null;

    #[ORM\ManyToOne(inversedBy: 'traceCompetences')]
    private ?ApcNiveau $apcNiveau = null;

    #[ORM\ManyToOne(inversedBy: 'traceCompetences')]
    private ?ApcApprentissageCritique $apcApprentissageCritique = null;

    #[ORM\ManyToOne(inversedBy: 'traceCompetences')]
    private ?PortfolioUniv $portfolio = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrace(): ?Trace
    {
        return $this->trace;
    }

    public function setTrace(?Trace $trace): static
    {
        $this->trace = $trace;

        return $this;
    }

    public function getApcNiveau(): ?ApcNiveau
    {
        return $this->apcNiveau;
    }

    public function setApcNiveau(?ApcNiveau $apcNiveau): static
    {
        $this->apcNiveau = $apcNiveau;

        return $this;
    }

    public function getApcApprentissageCritique(): ?ApcApprentissageCritique
    {
        return $this->apcApprentissageCritique;
    }

    public function setApcApprentissageCritique(?ApcApprentissageCritique $apcApprentissageCritique): static
    {
        $this->apcApprentissageCritique = $apcApprentissageCritique;

        return $this;
    }

    public function getPortfolio(): ?PortfolioUniv
    {
        return $this->portfolio;
    }

    public function setPortfolio(?PortfolioUniv $portfolio): static
    {
        $this->portfolio = $portfolio;

        return $this;
    }
}
