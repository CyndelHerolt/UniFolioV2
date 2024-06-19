<?php

namespace App\Entity;

use App\Repository\TraceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: TraceRepository::class)]
#[Broadcast]
class Trace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'traces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bibliotheque $bibliotheque = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_modification = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\Column]
    private array $contenu = [];

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $legende = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_realisation = null;

    #[ORM\Column(length: 255)]
    private ?string $contexte = null;

    #[ORM\OneToMany(targetEntity: TracePage::class, mappedBy: 'trace', orphanRemoval: true)]
    private Collection $tracePages;

    #[ORM\OneToMany(targetEntity: Validation::class, mappedBy: 'trace', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $validations;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'trace')]
    private Collection $commentaires;

    /**
     * @var Collection<int, TraceCompetence>
     */
    #[ORM\OneToMany(targetEntity: TraceCompetence::class, mappedBy: 'trace')]
    private Collection $traceCompetences;

    public function __construct()
    {
        $this->tracePages = new ArrayCollection();
        $this->validations = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->traceCompetences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBibliotheque(): ?Bibliotheque
    {
        return $this->bibliotheque;
    }

    public function setBibliotheque(?Bibliotheque $bibliotheque): static
    {
        $this->bibliotheque = $bibliotheque;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getContenu(): array
    {
        return $this->contenu;
    }

    public function setContenu(array $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLegende(): ?string
    {
        return $this->legende;
    }

    public function setLegende(?string $legende): static
    {
        $this->legende = $legende;

        return $this;
    }

    public function getDateRealisation(): ?\DateTimeInterface
    {
        return $this->date_realisation;
    }

    public function setDateRealisation(?\DateTimeInterface $date_realisation): static
    {
        $this->date_realisation = $date_realisation;

        return $this;
    }

    public function getContexte(): ?string
    {
        return $this->contexte;
    }

    public function setContexte(string $contexte): static
    {
        $this->contexte = $contexte;

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
            $tracePage->setTrace($this);
        }

        return $this;
    }

    public function removeTracePage(TracePage $tracePage): static
    {
        if ($this->tracePages->removeElement($tracePage)) {
            // set the owning side to null (unless already changed)
            if ($tracePage->getTrace() === $this) {
                $tracePage->setTrace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Validation>
     */
    public function getValidations(): Collection
    {
        return $this->validations;
    }

    public function addValidation(Validation $validation): static
    {
        if (!$this->validations->contains($validation)) {
            $this->validations->add($validation);
            $validation->setTrace($this);
        }

        return $this;
    }

    public function removeValidation(Validation $validation): static
    {
        if ($this->validations->removeElement($validation)) {
            // set the owning side to null (unless already changed)
            if ($validation->getTrace() === $this) {
                $validation->setTrace(null);
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
            $commentaire->setTrace($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getTrace() === $this) {
                $commentaire->setTrace(null);
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
            $traceCompetence->setTrace($this);
        }

        return $this;
    }

    public function removeTraceCompetence(TraceCompetence $traceCompetence): static
    {
        if ($this->traceCompetences->removeElement($traceCompetence)) {
            // set the owning side to null (unless already changed)
            if ($traceCompetence->getTrace() === $this) {
                $traceCompetence->setTrace(null);
            }
        }

        return $this;
    }
}
