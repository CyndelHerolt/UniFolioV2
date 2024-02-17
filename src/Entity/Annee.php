<?php

namespace App\Entity;

use App\Repository\AnneeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnneeRepository::class)]
class Annee
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle_long = null;

    #[ORM\Column]
    private ?bool $opt_alternance = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'annees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Diplome $diplome = null;

    #[ORM\OneToMany(targetEntity: Semestre::class, mappedBy: 'annee', orphanRemoval: true)]
    private Collection $semestres;

    #[ORM\OneToMany(targetEntity: ApcNiveau::class, mappedBy: 'annee')]
    private Collection $apcNiveaux;

    #[ORM\OneToMany(targetEntity: Bibliotheque::class, mappedBy: 'annee')]
    private Collection $bibliotheques;

    #[ORM\OneToMany(targetEntity: PortfolioUniv::class, mappedBy: 'annee')]
    private Collection $portfolioUnivs;

    public function __construct()
    {
        $this->semestres = new ArrayCollection();
        $this->apcNiveaux = new ArrayCollection();
        $this->bibliotheques = new ArrayCollection();
        $this->portfolioUnivs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setSemestres(Collection $semestres): void
    {
        $this->semestres = $semestres;
    }

    public function setApcNiveaux(Collection $apcNiveaux): void
    {
        $this->apcNiveaux = $apcNiveaux;
    }

    public function setBibliotheques(Collection $bibliotheques): void
    {
        $this->bibliotheques = $bibliotheques;
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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getLibelleLong(): ?string
    {
        return $this->libelle_long;
    }

    public function setLibelleLong(string $libelle_long): static
    {
        $this->libelle_long = $libelle_long;

        return $this;
    }

    public function isOptAlternance(): ?bool
    {
        return $this->opt_alternance;
    }

    public function setOptAlternance(bool $opt_alternance): static
    {
        $this->opt_alternance = $opt_alternance;

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

    public function getDiplome(): ?Diplome
    {
        return $this->diplome;
    }

    public function setDiplome(?Diplome $diplome): static
    {
        $this->diplome = $diplome;

        return $this;
    }

    /**
     * @return Collection<int, Semestre>
     */
    public function getSemestres(): Collection
    {
        return $this->semestres;
    }

    public function addSemestre(Semestre $semestre): static
    {
        if (!$this->semestres->contains($semestre)) {
            $this->semestres->add($semestre);
            $semestre->setAnnee($this);
        }

        return $this;
    }

    public function removeSemestre(Semestre $semestre): static
    {
        if ($this->semestres->removeElement($semestre)) {
            // set the owning side to null (unless already changed)
            if ($semestre->getAnnee() === $this) {
                $semestre->setAnnee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApcNiveau>
     */
    public function getApcNiveaux(): Collection
    {
        return $this->apcNiveaux;
    }

    public function addApcNiveau(ApcNiveau $apcNiveau): static
    {
        if (!$this->apcNiveaux->contains($apcNiveau)) {
            $this->apcNiveaux->add($apcNiveau);
            $apcNiveau->setAnnee($this);
        }

        return $this;
    }

    public function removeApcNiveau(ApcNiveau $apcNiveau): static
    {
        if ($this->apcNiveaux->removeElement($apcNiveau)) {
            // set the owning side to null (unless already changed)
            if ($apcNiveau->getAnnee() === $this) {
                $apcNiveau->setAnnee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bibliotheque>
     */
    public function getBibliotheques(): Collection
    {
        return $this->bibliotheques;
    }

    public function addBibliotheque(Bibliotheque $bibliotheque): static
    {
        if (!$this->bibliotheques->contains($bibliotheque)) {
            $this->bibliotheques->add($bibliotheque);
            $bibliotheque->setAnnee($this);
        }

        return $this;
    }

    public function removeBibliotheque(Bibliotheque $bibliotheque): static
    {
        if ($this->bibliotheques->removeElement($bibliotheque)) {
            // set the owning side to null (unless already changed)
            if ($bibliotheque->getAnnee() === $this) {
                $bibliotheque->setAnnee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PortfolioUniv>
     */
    public function getPortfolioUnivs(): Collection
    {
        return $this->portfolioUnivs;
    }

    public function addPortfolioUniv(PortfolioUniv $portfolioUniv): static
    {
        if (!$this->portfolioUnivs->contains($portfolioUniv)) {
            $this->portfolioUnivs->add($portfolioUniv);
            $portfolioUniv->setAnnee($this);
        }

        return $this;
    }

    public function removePortfolioUniv(PortfolioUniv $portfolioUniv): static
    {
        if ($this->portfolioUnivs->removeElement($portfolioUniv)) {
            // set the owning side to null (unless already changed)
            if ($portfolioUniv->getAnnee() === $this) {
                $portfolioUniv->setAnnee(null);
            }
        }

        return $this;
    }
}
