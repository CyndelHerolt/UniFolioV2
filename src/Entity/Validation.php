<?php

namespace App\Entity;

use App\Repository\ValidationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: ValidationRepository::class)]
#[Broadcast]
class Validation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_modification = null;

    #[ORM\Column]
    private ?int $etat = null;

    #[ORM\ManyToOne(inversedBy: 'validations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trace $trace = null;

    #[ORM\ManyToOne(inversedBy: 'validations')]
    #[ORM\JoinColumn(nullable: true)]
    private ?ApcNiveau $apcNiveau = null;

    #[ORM\ManyToOne(inversedBy: 'validations')]
    private ?Enseignant $enseignant = null;

    #[ORM\ManyToOne(inversedBy: 'validations')]
    private ?ApcApprentissageCritique $apc_apprentissage_critique = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->date_modification;
    }

    public function setDateModification(?\DateTimeInterface $date_modification): static
    {
        $this->date_modification = $date_modification;

        return $this;
    }

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(int $etat): static
    {
        $this->etat = $etat;

        return $this;
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

    public function getEnseignant(): ?Enseignant
    {
        return $this->enseignant;
    }

    public function setEnseignant(?Enseignant $enseignant): static
    {
        $this->enseignant = $enseignant;

        return $this;
    }

    public function getApcApprentissageCritique(): ?ApcApprentissageCritique
    {
        return $this->apc_apprentissage_critique;
    }

    public function setApcApprentissageCritique(?ApcApprentissageCritique $apc_apprentissage_critique): static
    {
        $this->apc_apprentissage_critique = $apc_apprentissage_critique;

        return $this;
    }
}
