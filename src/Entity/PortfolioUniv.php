<?php

namespace App\Entity;

use App\Repository\PortfolioUnivRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: PortfolioUnivRepository::class)]
#[Broadcast]
class PortfolioUniv
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_modification = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?bool $visibilite = null;

    #[ORM\Column(length: 255)]
    private ?string $banniere = '/files_directory/banniere.jpg';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $opt_search = null;

    #[ORM\ManyToOne(inversedBy: 'portfolioUnivs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etudiant $etudiant = null;

    #[ORM\ManyToOne(inversedBy: 'portfolioUnivs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Annee $annee = null;

    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'portfolio', orphanRemoval: true)]
    private Collection $pages;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'portfolio')]
    private Collection $commentaires;

    /**
     * @var Collection<int, TraceCompetence>
     */
    #[ORM\OneToMany(targetEntity: TraceCompetence::class, mappedBy: 'portfolio', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $traceCompetences;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->traceCompetences = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->libelle;
    }

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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function isVisibilite(): ?bool
    {
        return $this->visibilite;
    }

    public function setVisibilite(bool $visibilite): static
    {
        $this->visibilite = $visibilite;

        return $this;
    }

    public function getBanniere(): ?string
    {
        return $this->banniere;
    }

    public function setBanniere(string $banniere): static
    {
        $this->banniere = $banniere;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isOptSearch(): ?bool
    {
        return $this->opt_search;
    }

    public function setOptSearch(bool $opt_search): static
    {
        $this->opt_search = $opt_search;

        return $this;
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(?Etudiant $etudiant): static
    {
        $this->etudiant = $etudiant;

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
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setPortfolio($this);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getPortfolio() === $this) {
                $page->setPortfolio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setPortfolio($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getPortfolio() === $this) {
                $commentaire->setPortfolio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TraceCompetence>
     */
    public function getTraceCompetences(): Collection
    {
        return $this->traceCompetences;
    }

    public function addTraceCompetence(TraceCompetence $traceCompetence): static
    {
        if (!$this->traceCompetences->contains($traceCompetence)) {
            $this->traceCompetences->add($traceCompetence);
            $traceCompetence->setPortfolio($this);
        }

        return $this;
    }

    public function removeTraceCompetence(TraceCompetence $traceCompetence): static
    {
        if ($this->traceCompetences->removeElement($traceCompetence)) {
            // set the owning side to null (unless already changed)
            if ($traceCompetence->getPortfolio() === $this) {
                $traceCompetence->setPortfolio(null);
            }
        }

        return $this;
    }
}
