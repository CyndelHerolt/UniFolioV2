<?php

namespace App\Entity;

use App\Repository\AnneeUniversitaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnneeUniversitaireRepository::class)]
class AnneeUniversitaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\OneToMany(targetEntity: Bibliotheque::class, mappedBy: 'anneeUniversitaire')]
    private Collection $bibliotheques;

    #[ORM\Column]
    private ?int $annee = null;

    /**
     * @var Collection<int, PortfolioUniv>
     */
    #[ORM\OneToMany(targetEntity: PortfolioUniv::class, mappedBy: 'anneeUniv')]
    private Collection $portfolioUnivs;

    public function __construct()
    {
        $this->bibliotheques = new ArrayCollection();
        $this->portfolioUnivs = new ArrayCollection();
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

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
            $bibliotheque->setAnneeUniversitaire($this);
        }

        return $this;
    }

    public function removeBibliotheque(Bibliotheque $bibliotheque): static
    {
        if ($this->bibliotheques->removeElement($bibliotheque)) {
            // set the owning side to null (unless already changed)
            if ($bibliotheque->getAnneeUniversitaire() === $this) {
                $bibliotheque->setAnneeUniversitaire(null);
            }
        }

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

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
            $portfolioUniv->setAnneeUniv($this);
        }

        return $this;
    }

    public function removePortfolioUniv(PortfolioUniv $portfolioUniv): static
    {
        if ($this->portfolioUnivs->removeElement($portfolioUniv)) {
            // set the owning side to null (unless already changed)
            if ($portfolioUniv->getAnneeUniv() === $this) {
                $portfolioUniv->setAnneeUniv(null);
            }
        }

        return $this;
    }
}
