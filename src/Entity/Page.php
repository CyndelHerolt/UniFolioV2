<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[Broadcast]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PortfolioUniv $portfolio = null;

    #[ORM\OneToMany(targetEntity: TracePage::class, mappedBy: 'page', orphanRemoval: true)]
    private Collection $tracePages;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    private ?ApcNiveau $apc_niveau = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    private ?ApcApprentissageCritique $apc_apprentissage_critique = null;

    /**
     * @var Collection<int, CritereApprentissageCritique>
     */
    #[ORM\OneToMany(targetEntity: CritereApprentissageCritique::class, mappedBy: 'page', orphanRemoval: true)]
    private Collection $critereApprentissageCritiques;

    /**
     * @var Collection<int, CritereNiveau>
     */
    #[ORM\OneToMany(targetEntity: CritereNiveau::class, mappedBy: 'page', orphanRemoval: true)]
    private Collection $critereNiveaux;

    public function __construct()
    {
        $this->tracePages = new ArrayCollection();
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

    public function getPortfolio(): ?PortfolioUniv
    {
        return $this->portfolio;
    }

    public function setPortfolio(?PortfolioUniv $portfolio): static
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * @return Collection<int, TracePage>
     */
    public function getTracePages(): Collection
    {
        return $this->tracePages;
    }

    public function addTracePage(TracePage $tracePage): static
    {
        if (!$this->tracePages->contains($tracePage)) {
            $this->tracePages->add($tracePage);
            $tracePage->setPage($this);
        }

        return $this;
    }

    public function removeTracePage(TracePage $tracePage): static
    {
        if ($this->tracePages->removeElement($tracePage)) {
            // set the owning side to null (unless already changed)
            if ($tracePage->getPage() === $this) {
                $tracePage->setPage(null);
            }
        }

        return $this;
    }

    public function getApcNiveau(): ?ApcNiveau
    {
        return $this->apc_niveau;
    }

    public function setApcNiveau(?ApcNiveau $apc_niveau): static
    {
        $this->apc_niveau = $apc_niveau;

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
            $critereApprentissageCritique->setPage($this);
        }

        return $this;
    }

    public function removeCritereApprentissageCritique(CritereApprentissageCritique $critereApprentissageCritique): static
    {
        if ($this->critereApprentissageCritiques->removeElement($critereApprentissageCritique)) {
            // set the owning side to null (unless already changed)
            if ($critereApprentissageCritique->getPage() === $this) {
                $critereApprentissageCritique->setPage(null);
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
            $critereNiveau->setPage($this);
        }

        return $this;
    }

    public function removeCritereNiveau(CritereNiveau $critereNiveau): static
    {
        if ($this->critereNiveaux->removeElement($critereNiveau)) {
            // set the owning side to null (unless already changed)
            if ($critereNiveau->getPage() === $this) {
                $critereNiveau->setPage(null);
            }
        }

        return $this;
    }
}
