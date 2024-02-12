<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupeRepository::class)]
class Groupe
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 50)]
    private ?string $codeApogee = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'groupes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeGroupe $typeGroupe = null;

    #[ORM\ManyToOne(inversedBy: 'groupes')]
    private ?ApcParcours $apcParcours = null;

    #[ORM\ManyToMany(targetEntity: Etudiant::class, mappedBy: 'groupe')]
    private Collection $etudiants;

    public function __construct()
    {
        $this->etudiants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setEtudiants(Collection $etudiants): void
    {
        $this->etudiants = $etudiants;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCodeApogee(): ?string
    {
        return $this->codeApogee;
    }

    public function setCodeApogee(?string $codeApogee): static
    {
        $this->codeApogee = $codeApogee;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getTypeGroupe(): ?TypeGroupe
    {
        return $this->typeGroupe;
    }

    public function setTypeGroupe(?TypeGroupe $typeGroupe): static
    {
        $this->typeGroupe = $typeGroupe;

        return $this;
    }

    public function getApcParcours(): ?ApcParcours
    {
        return $this->apcParcours;
    }

    public function setApcParcours(?ApcParcours $apcParcours): static
    {
        $this->apcParcours = $apcParcours;

        return $this;
    }

    /**
     * @return Collection<int, Etudiant>
     */
    public function getEtudiants(): Collection
    {
        return $this->etudiants;
    }

    public function addEtudiant(Etudiant $etudiant): static
    {
        if (!$this->etudiants->contains($etudiant)) {
            $this->etudiants->add($etudiant);
            $etudiant->addGroupe($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): static
    {
        if ($this->etudiants->removeElement($etudiant)) {
            $etudiant->removeGroupe($this);
        }

        return $this;
    }
}
