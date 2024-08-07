<?php

namespace App\Entity;

use App\Repository\ApcNiveauRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApcNiveauRepository::class)]
class ApcNiveau
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'apcNiveaux')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Annee $annee = null;

    #[ORM\ManyToMany(targetEntity: ApcParcours::class, inversedBy: 'apcNiveaux')]
    private Collection $apcParcours;

    #[ORM\ManyToOne(inversedBy: 'apcNiveau')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ApcCompetence $apcCompetence = null;

    #[ORM\OneToMany(targetEntity: ApcApprentissageCritique::class, mappedBy: 'apcNiveau')]
    private Collection $apcApprentissageCritiques;

    #[ORM\Column(nullable: true)]
    private ?bool $actif = null;

    #[ORM\ManyToMany(targetEntity: Criteres::class, mappedBy: 'apcNiveau')]
    private Collection $criteres;

    /**
     * @var Collection<int, TraceCompetence>
     */
    #[ORM\OneToMany(targetEntity: TraceCompetence::class, mappedBy: 'apcNiveau')]
    private Collection $traceCompetences;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'apc_niveau')]
    private Collection $pages;

    /**
     * @var Collection<int, CritereNiveau>
     */
    #[ORM\OneToMany(targetEntity: CritereNiveau::class, mappedBy: 'apcNiveau')]
    private Collection $critereNiveaux;

    public function __construct()
    {
        $this->apcParcours = new ArrayCollection();
        $this->apcApprentissageCritiques = new ArrayCollection();
        $this->criteres = new ArrayCollection();
        $this->traceCompetences = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->critereNiveaux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setApcParcours(Collection $apcParcours): void
    {
        $this->apcParcours = $apcParcours;
    }

    public function setApcApprentissageCritiques(Collection $apcApprentissageCritiques): void
    {
        $this->apcApprentissageCritiques = $apcApprentissageCritiques;
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
     * @return Collection<int, ApcParcours>
     */
    public function getApcParcours(): Collection
    {
        return $this->apcParcours;
    }

    public function addApcParcour(ApcParcours $apcParcour): static
    {
        if (!$this->apcParcours->contains($apcParcour)) {
            $this->apcParcours->add($apcParcour);
        }

        return $this;
    }

    public function removeApcParcour(ApcParcours $apcParcour): static
    {
        $this->apcParcours->removeElement($apcParcour);

        return $this;
    }

    public function getApcCompetence(): ?ApcCompetence
    {
        return $this->apcCompetence;
    }

    public function setApcCompetence(?ApcCompetence $apcCompetence): static
    {
        $this->apcCompetence = $apcCompetence;

        return $this;
    }

    /**
     * @return Collection<int, ApcApprentissageCritique>
     */
    public function getApcApprentissageCritiques(): Collection
    {
        return $this->apcApprentissageCritiques;
    }

    public function addApcApprentissageCritique(ApcApprentissageCritique $apcApprentissageCritique): static
    {
        if (!$this->apcApprentissageCritiques->contains($apcApprentissageCritique)) {
            $this->apcApprentissageCritiques->add($apcApprentissageCritique);
            $apcApprentissageCritique->setApcNiveau($this);
        }

        return $this;
    }

    public function removeApcApprentissageCritique(ApcApprentissageCritique $apcApprentissageCritique): static
    {
        if ($this->apcApprentissageCritiques->removeElement($apcApprentissageCritique)) {
            // set the owning side to null (unless already changed)
            if ($apcApprentissageCritique->getApcNiveau() === $this) {
                $apcApprentissageCritique->setApcNiveau(null);
            }
        }

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * @return Collection<int, Criteres>
     */
    public function getCriteres(): Collection
    {
        return $this->criteres;
    }

    public function addCritere(Criteres $critere): static
    {
        if (!$this->criteres->contains($critere)) {
            $this->criteres->add($critere);
            $critere->addApcNiveau($this);
        }

        return $this;
    }

    public function removeCritere(Criteres $critere): static
    {
        if ($this->criteres->removeElement($critere)) {
            $critere->removeApcNiveau($this);
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
            $traceCompetence->setApcNiveau($this);
        }

        return $this;
    }

    public function removeTraceCompetence(TraceCompetence $traceCompetence): static
    {
        if ($this->traceCompetences->removeElement($traceCompetence)) {
            // set the owning side to null (unless already changed)
            if ($traceCompetence->getApcNiveau() === $this) {
                $traceCompetence->setApcNiveau(null);
            }
        }

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
            $page->setApcNiveau($this);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getApcNiveau() === $this) {
                $page->setApcNiveau(null);
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
            $critereNiveau->setApcNiveau($this);
        }

        return $this;
    }

    public function removeCritereNiveau(CritereNiveau $critereNiveau): static
    {
        if ($this->critereNiveaux->removeElement($critereNiveau)) {
            // set the owning side to null (unless already changed)
            if ($critereNiveau->getApcNiveau() === $this) {
                $critereNiveau->setApcNiveau(null);
            }
        }

        return $this;
    }
}
