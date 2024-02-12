<?php

namespace App\Entity;

use App\Repository\ApcReferentielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApcReferentielRepository::class)]
class ApcReferentiel
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setApcParcours(Collection $apcParcours): void
    {
        $this->apcParcours = $apcParcours;
    }

    public function setApcCompetences(Collection $apcCompetences): void
    {
        $this->apcCompetences = $apcCompetences;
    }

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $anneePublication = null;

    #[ORM\ManyToOne(inversedBy: 'apcReferentiels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Departement $departement = null;

    #[ORM\OneToMany(targetEntity: ApcParcours::class, mappedBy: 'apcReferentiel')]
    private Collection $apcParcours;

    #[ORM\OneToMany(targetEntity: ApcCompetence::class, mappedBy: 'apcReferentiel')]
    private Collection $apcCompetences;

    public function __construct()
    {
        $this->apcParcours = new ArrayCollection();
        $this->apcCompetences = new ArrayCollection();
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

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAnneePublication(): ?int
    {
        return $this->anneePublication;
    }

    public function setAnneePublication(int $anneePublication): static
    {
        $this->anneePublication = $anneePublication;

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
            $apcParcour->setApcReferentiel($this);
        }

        return $this;
    }

    public function removeApcParcour(ApcParcours $apcParcour): static
    {
        if ($this->apcParcours->removeElement($apcParcour)) {
            // set the owning side to null (unless already changed)
            if ($apcParcour->getApcReferentiel() === $this) {
                $apcParcour->setApcReferentiel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApcCompetence>
     */
    public function getApcCompetences(): Collection
    {
        return $this->apcCompetences;
    }

    public function addApcCompetence(ApcCompetence $apcCompetence): static
    {
        if (!$this->apcCompetences->contains($apcCompetence)) {
            $this->apcCompetences->add($apcCompetence);
            $apcCompetence->setApcReferentiel($this);
        }

        return $this;
    }

    public function removeApcCompetence(ApcCompetence $apcCompetence): static
    {
        if ($this->apcCompetences->removeElement($apcCompetence)) {
            // set the owning side to null (unless already changed)
            if ($apcCompetence->getApcReferentiel() === $this) {
                $apcCompetence->setApcReferentiel(null);
            }
        }

        return $this;
    }
}
