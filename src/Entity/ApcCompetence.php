<?php

namespace App\Entity;

use App\Repository\ApcCompetenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApcCompetenceRepository::class)]
class ApcCompetence
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setApcNiveau(Collection $apcNiveau): void
    {
        $this->apcNiveau = $apcNiveau;
    }

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 50)]
    private ?string $nomCourt = null;

    #[ORM\Column(length: 20)]
    private ?string $couleur = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $ue = null;

    #[ORM\ManyToOne(inversedBy: 'apcCompetences')]
    #[ORM\JoinColumn(nullable: true)]
    private ?ApcReferentiel $apcReferentiel = null;

    #[ORM\OneToMany(targetEntity: ApcNiveau::class, mappedBy: 'apcCompetence')]
    private Collection $apcNiveau;

    public function __construct()
    {
        $this->apcNiveau = new ArrayCollection();
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

    public function getNomCourt(): ?string
    {
        return $this->nomCourt;
    }

    public function setNomCourt(string $nomCourt): static
    {
        $this->nomCourt = $nomCourt;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;

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

    public function getUe(): ?string
    {
        return $this->ue;
    }

    public function setUe(string $ue): static
    {
        $this->ue = $ue;

        return $this;
    }

    public function getApcReferentiel(): ?ApcReferentiel
    {
        return $this->apcReferentiel;
    }

    public function setApcReferentiel(?ApcReferentiel $apcReferentiel): static
    {
        $this->apcReferentiel = $apcReferentiel;

        return $this;
    }

    /**
     * @return Collection<int, ApcNiveau>
     */
    public function getApcNiveau(): Collection
    {
        return $this->apcNiveau;
    }

    public function addApcNiveau(ApcNiveau $apcNiveau): static
    {
        if (!$this->apcNiveau->contains($apcNiveau)) {
            $this->apcNiveau->add($apcNiveau);
            $apcNiveau->setApcCompetence($this);
        }

        return $this;
    }

    public function removeApcNiveau(ApcNiveau $apcNiveau): static
    {
        if ($this->apcNiveau->removeElement($apcNiveau)) {
            // set the owning side to null (unless already changed)
            if ($apcNiveau->getApcCompetence() === $this) {
                $apcNiveau->setApcCompetence(null);
            }
        }

        return $this;
    }
}
