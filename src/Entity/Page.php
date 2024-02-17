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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PortfolioUniv $portfolio = null;

    #[ORM\OneToMany(targetEntity: TracePage::class, mappedBy: 'page', orphanRemoval: true)]
    private Collection $tracePages;

    public function __construct()
    {
        $this->tracePages = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
}
