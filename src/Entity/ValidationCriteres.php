<?php

namespace App\Entity;

use App\Repository\ValidationCriteresRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValidationCriteresRepository::class)]
class ValidationCriteres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $valeur = null;

    #[ORM\ManyToOne(inversedBy: 'validationCriteres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Criteres $critere = null;

    #[ORM\ManyToOne(inversedBy: 'validationCriteres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Validation $validation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeur(): ?float
    {
        return $this->valeur;
    }

    public function setValeur(float $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
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

    public function getValidation(): ?Validation
    {
        return $this->validation;
    }

    public function setValidation(?Validation $validation): static
    {
        $this->validation = $validation;

        return $this;
    }
}
