<?php

namespace App\Entity;

use App\Repository\SemestreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SemestreRepository::class)]
class Semestre
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?int $ordre_annee = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column]
    private ?int $ordre_lmd = null;

    #[ORM\Column(length: 20)]
    private ?string $code_element = null;

    #[ORM\ManyToOne(inversedBy: 'semestres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Annee $annee = null;

    #[ORM\ManyToMany(targetEntity: TypeGroupe::class, mappedBy: 'semestre')]
    private Collection $typeGroupes;

    #[ORM\OneToMany(targetEntity: Etudiant::class, mappedBy: 'semestre')]
    private Collection $etudiants;

    public function __construct()
    {
        $this->typeGroupes = new ArrayCollection();
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

    public function setTypeGroupes(Collection $typeGroupes): void
    {
        $this->typeGroupes = $typeGroupes;
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

    public function getOrdreAnnee(): ?int
    {
        return $this->ordre_annee;
    }

    public function setOrdreAnnee(int $ordre_annee): static
    {
        $this->ordre_annee = $ordre_annee;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getOrdreLmd(): ?int
    {
        return $this->ordre_lmd;
    }

    public function setOrdreLmd(int $ordre_lmd): static
    {
        $this->ordre_lmd = $ordre_lmd;

        return $this;
    }

    public function getCodeElement(): ?string
    {
        return $this->code_element;
    }

    public function setCodeElement(string $code_element): static
    {
        $this->code_element = $code_element;

        return $this;
    }

    public function getAnnee(): ?Annee
    {
        return $this->annee;
    }

    public function setAnnee(?Annee $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * @return Collection<int, TypeGroupe>
     */
    public function getTypeGroupes(): Collection
    {
        return $this->typeGroupes;
    }

    public function addTypeGroupe(TypeGroupe $typeGroupe): static
    {
        if (!$this->typeGroupes->contains($typeGroupe)) {
            $this->typeGroupes->add($typeGroupe);
            $typeGroupe->addSemestre($this);
        }

        return $this;
    }

    public function removeTypeGroupe(TypeGroupe $typeGroupe): static
    {
        if ($this->typeGroupes->removeElement($typeGroupe)) {
            $typeGroupe->removeSemestre($this);
        }

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
            $etudiant->setSemestre($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): static
    {
        if ($this->etudiants->removeElement($etudiant)) {
            // set the owning side to null (unless already changed)
            if ($etudiant->getSemestre() === $this) {
                $etudiant->setSemestre(null);
            }
        }

        return $this;
    }
}
