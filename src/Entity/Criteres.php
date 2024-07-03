<?php

namespace App\Entity;

use App\Repository\CriteresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CriteresRepository::class)]
class Criteres
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'criteres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Departement $departement = null;

    #[ORM\Column]
    private array $valeurs = [];

    /**
     * @var Collection<int, CritereApprentissageCritique>
     */
    #[ORM\OneToMany(targetEntity: CritereApprentissageCritique::class, mappedBy: 'critere')]
    private Collection $critereApprentissageCritiques;

    /**
     * @var Collection<int, CritereNiveau>
     */
    #[ORM\OneToMany(targetEntity: CritereNiveau::class, mappedBy: 'critere')]
    private Collection $critereNiveaux;

    public function __construct()
    {
        $this->critereApprentissageCritiques = new ArrayCollection();
        $this->critereNiveaux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getValeurs(): array
    {
        return $this->valeurs;
    }

    public function setValeurs(array $valeurs): static
    {
        $this->valeurs = $valeurs;

        return $this;
    }

    /**
     * @return Collection<int, CritereApprentissageCritique>
     */
    public function getCritereApprentissageCritiques(): Collection
    {
        return $this->critereApprentissageCritiques;
    }

    public function addCritereApprentissageCritique(CritereApprentissageCritique $critereApprentissageCritique): static
    {
        if (!$this->critereApprentissageCritiques->contains($critereApprentissageCritique)) {
            $this->critereApprentissageCritiques->add($critereApprentissageCritique);
            $critereApprentissageCritique->setCritere($this);
        }

        return $this;
    }

    public function removeCritereApprentissageCritique(CritereApprentissageCritique $critereApprentissageCritique): static
    {
        if ($this->critereApprentissageCritiques->removeElement($critereApprentissageCritique)) {
            // set the owning side to null (unless already changed)
            if ($critereApprentissageCritique->getCritere() === $this) {
                $critereApprentissageCritique->setCritere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CritereNiveau>
     */
    public function getCritereNiveaux(): Collection
    {
        return $this->critereNiveaux;
    }

    public function addCritereNiveau(CritereNiveau $critereNiveau): static
    {
        if (!$this->critereNiveaux->contains($critereNiveau)) {
            $this->critereNiveaux->add($critereNiveau);
            $critereNiveau->setCritere($this);
        }

        return $this;
    }

    public function removeCritereNiveau(CritereNiveau $critereNiveau): static
    {
        if ($this->critereNiveaux->removeElement($critereNiveau)) {
            // set the owning side to null (unless already changed)
            if ($critereNiveau->getCritere() === $this) {
                $critereNiveau->setCritere(null);
            }
        }

        return $this;
    }
}
