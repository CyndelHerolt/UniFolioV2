<?php

namespace App\Entity;

use App\Repository\ApcApprentissageCritiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApcApprentissageCritiqueRepository::class)]
class ApcApprentissageCritique
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    #[ORM\Column(type: Types::TEXT)]
    private ?string $libelle = null;

    #[ORM\Column(length: 20)]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'apcApprentissageCritiques')]
    private ?ApcNiveau $apcNiveau = null;

    #[ORM\Column(nullable: true)]
    private ?bool $actif = null;

    #[ORM\ManyToMany(targetEntity: Criteres::class, mappedBy: 'apcApprentissageCritique')]
    private Collection $criteres;

    /**
     * @var Collection<int, TraceCompetence>
     */
    #[ORM\OneToMany(targetEntity: TraceCompetence::class, mappedBy: 'apcApprentissageCritique')]
    private Collection $traceCompetences;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'apc_apprentissage_critique')]
    private Collection $pages;

    /**
     * @var Collection<int, CritereApprentissageCritique>
     */
    #[ORM\OneToMany(targetEntity: CritereApprentissageCritique::class, mappedBy: 'apprentissageCritique')]
    private Collection $critereApprentissageCritiques;

    public function __construct()
    {
        $this->criteres = new ArrayCollection();
        $this->traceCompetences = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->critereApprentissageCritiques = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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
            $critere->addApcApprentissageCritique($this);
        }

        return $this;
    }

    public function removeCritere(Criteres $critere): static
    {
        if ($this->criteres->removeElement($critere)) {
            $critere->removeApcApprentissageCritique($this);
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
            $traceCompetence->setApcApprentissageCritique($this);
        }

        return $this;
    }

    public function removeTraceCompetence(TraceCompetence $traceCompetence): static
    {
        if ($this->traceCompetences->removeElement($traceCompetence)) {
            // set the owning side to null (unless already changed)
            if ($traceCompetence->getApcApprentissageCritique() === $this) {
                $traceCompetence->setApcApprentissageCritique(null);
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
            $page->setApcApprentissageCritique($this);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getApcApprentissageCritique() === $this) {
                $page->setApcApprentissageCritique(null);
            }
        }

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
            $critereApprentissageCritique->setApprentissageCritique($this);
        }

        return $this;
    }

    public function removeCritereApprentissageCritique(CritereApprentissageCritique $critereApprentissageCritique): static
    {
        if ($this->critereApprentissageCritiques->removeElement($critereApprentissageCritique)) {
            // set the owning side to null (unless already changed)
            if ($critereApprentissageCritique->getApprentissageCritique() === $this) {
                $critereApprentissageCritique->setApprentissageCritique(null);
            }
        }

        return $this;
    }
}
