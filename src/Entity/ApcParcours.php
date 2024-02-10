<?php

namespace App\Entity;

use App\Repository\ApcParcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApcParcoursRepository::class)]
class ApcParcours
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setApcNiveaux(Collection $apcNiveaux): void
    {
        $this->apcNiveaux = $apcNiveaux;
    }

    public function setGroupes(Collection $groupes): void
    {
        $this->groupes = $groupes;
    }

    public function setDiplomes(Collection $diplomes): void
    {
        $this->diplomes = $diplomes;
    }

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 10)]
    private ?string $code = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column]
    private ?bool $formationContinue = null;

    #[ORM\ManyToOne(inversedBy: 'apcParcours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ApcReferentiel $apcReferentiel = null;

    #[ORM\ManyToMany(targetEntity: ApcNiveau::class, mappedBy: 'apcParcours')]
    private Collection $apcNiveaux;

    #[ORM\OneToMany(targetEntity: Groupe::class, mappedBy: 'apcParcours')]
    private Collection $groupes;

    #[ORM\OneToMany(targetEntity: Diplome::class, mappedBy: 'apcParcours')]
    private Collection $diplomes;

    public function __construct()
    {
        $this->apcNiveaux = new ArrayCollection();
        $this->groupes = new ArrayCollection();
        $this->diplomes = new ArrayCollection();
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

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function isFormationContinue(): ?bool
    {
        return $this->formationContinue;
    }

    public function setFormationContinue(bool $formationContinue): static
    {
        $this->formationContinue = $formationContinue;

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
    public function getApcNiveaux(): Collection
    {
        return $this->apcNiveaux;
    }

    public function addApcNiveau(ApcNiveau $apcNiveau): static
    {
        if (!$this->apcNiveaux->contains($apcNiveau)) {
            $this->apcNiveaux->add($apcNiveau);
            $apcNiveau->addApcParcour($this);
        }

        return $this;
    }

    public function removeApcNiveau(ApcNiveau $apcNiveau): static
    {
        if ($this->apcNiveaux->removeElement($apcNiveau)) {
            $apcNiveau->removeApcParcour($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Groupe>
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function addGroupe(Groupe $groupe): static
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes->add($groupe);
            $groupe->setApcParcours($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): static
    {
        if ($this->groupes->removeElement($groupe)) {
            // set the owning side to null (unless already changed)
            if ($groupe->getApcParcours() === $this) {
                $groupe->setApcParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Diplome>
     */
    public function getDiplomes(): Collection
    {
        return $this->diplomes;
    }

    public function addDiplome(Diplome $diplome): static
    {
        if (!$this->diplomes->contains($diplome)) {
            $this->diplomes->add($diplome);
            $diplome->setApcParcours($this);
        }

        return $this;
    }

    public function removeDiplome(Diplome $diplome): static
    {
        if ($this->diplomes->removeElement($diplome)) {
            // set the owning side to null (unless already changed)
            if ($diplome->getApcParcours() === $this) {
                $diplome->setApcParcours(null);
            }
        }

        return $this;
    }
}
