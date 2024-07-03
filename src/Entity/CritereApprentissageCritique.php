<?php

namespace App\Entity;

use App\Repository\CritereApprentissageCritiqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CritereApprentissageCritiqueRepository::class)]
class CritereApprentissageCritique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'critereApprentissageCritiques')]
    private ?Criteres $critere = null;

    #[ORM\ManyToOne(inversedBy: 'critereApprentissageCritiques')]
    private ?ApcApprentissageCritique $apprentissageCritique = null;

    #[ORM\ManyToOne(inversedBy: 'critereApprentissageCritiques')]
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

    public function getApprentissageCritique(): ?ApcApprentissageCritique
    {
        return $this->apprentissageCritique;
    }

    public function setApprentissageCritique(?ApcApprentissageCritique $apprentissageCritique): static
    {
        $this->apprentissageCritique = $apprentissageCritique;

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
