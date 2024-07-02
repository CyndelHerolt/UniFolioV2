<?php

namespace App\Entity;

use App\Repository\CritereNiveauRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CritereNiveauRepository::class)]
class CritereNiveau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'critereNiveaux')]
    private ?Criteres $critere = null;

    #[ORM\ManyToOne(inversedBy: 'critereNiveaux')]
    private ?ApcNiveau $apcNiveau = null;

    #[ORM\ManyToOne(inversedBy: 'critereNiveaux')]
    private ?Page $page = null;

    #[ORM\Column(nullable: true)]
    private ?int $valeur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCritere(): ?Criteres
    {
        return $this->critere;
    }

    public function setCritere(?Criteres $critere): static
    {
        $this->critere = $critere;

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

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getValeur(): ?int
    {
        return $this->valeur;
    }

    public function setValeur(?int $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }
}
