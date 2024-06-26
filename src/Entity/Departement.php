<?php

namespace App\Entity;

use App\Repository\DepartementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartementRepository::class)]
class Departement
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $logoName = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $couleur = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, DepartementEnseignant>
     */
    #[ORM\OneToMany(mappedBy: 'departement', targetEntity: DepartementEnseignant::class, cascade: ['persist', 'remove'])]
    private Collection $departementEnseignants;

    #[ORM\OneToMany(targetEntity: Diplome::class, mappedBy: 'departement', orphanRemoval: true)]
    private Collection $diplomes;

    #[ORM\OneToMany(targetEntity: ApcReferentiel::class, mappedBy: 'departement')]
    private Collection $apcReferentiels;

    #[ORM\OneToMany(targetEntity: Criteres::class, mappedBy: 'departement', orphanRemoval: true)]
    private Collection $criteres;

    #[ORM\Column]
    private ?int $opt_competence = 1;

    public function __construct()
    {
        $this->enseignant = new ArrayCollection();
        $this->diplomes = new ArrayCollection();
        $this->apcReferentiels = new ArrayCollection();
        $this->criteres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setEnseignant(ArrayCollection $enseignant): void
    {
        $this->enseignant = $enseignant;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setDepartementEnseignants(Collection $departementEnseignants): void
    {
        $this->departementEnseignants = $departementEnseignants;
    }

    public function setDiplomes(Collection $diplomes): void
    {
        $this->diplomes = $diplomes;
    }

    public function setApcReferentiels(Collection $apcReferentiels): void
    {
        $this->apcReferentiels = $apcReferentiels;
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

    public function getLogoName(): ?string
    {
        return $this->logoName;
    }

    public function setLogoName(?string $logoName): static
    {
        $this->logoName = $logoName;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): static
    {
        $this->couleur = $couleur;

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

    /**
     * @return Collection|DepartementEnseignant[]
     */
    public function getDepartementEnseignants(): Collection
    {
        return $this->departementEnseignants;
    }

    public function addDepartementEnseignant(DepartementEnseignant $departementEnseignant): self
    {
        if (!$this->departementEnseignants->contains($departementEnseignant)) {
            $this->departementEnseignants[] = $departementEnseignant;
            $departementEnseignant->setDepartement($this);
        }

        return $this;
    }

    public function removeDepartementEnseignant(DepartementEnseignant $departementEnseignant): self
    {
        if ($this->departementEnseignants->contains($departementEnseignant)) {
            $this->departementEnseignants->removeElement($departementEnseignant);
            // set the owning side to null (unless already changed)
            if ($departementEnseignant->getDepartement() === $this) {
                $departementEnseignant->setDepartement(null);
            }
        }

        return $this;
    }

    public function removeEnseignant(Enseignant $enseignant): static
    {
        $this->enseignant->removeElement($enseignant);

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
            $diplome->setDepartement($this);
        }

        return $this;
    }

    public function removeDiplome(Diplome $diplome): static
    {
        if ($this->diplomes->removeElement($diplome)) {
            // set the owning side to null (unless already changed)
            if ($diplome->getDepartement() === $this) {
                $diplome->setDepartement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApcReferentiel>
     */
    public function getApcReferentiels(): Collection
    {
        return $this->apcReferentiels;
    }

    public function addApcReferentiel(ApcReferentiel $apcReferentiel): static
    {
        if (!$this->apcReferentiels->contains($apcReferentiel)) {
            $this->apcReferentiels->add($apcReferentiel);
            $apcReferentiel->setDepartement($this);
        }

        return $this;
    }

    public function removeApcReferentiel(ApcReferentiel $apcReferentiel): static
    {
        if ($this->apcReferentiels->removeElement($apcReferentiel)) {
            // set the owning side to null (unless already changed)
            if ($apcReferentiel->getDepartement() === $this) {
                $apcReferentiel->setDepartement(null);
            }
        }

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
            $critere->setDepartement($this);
        }

        return $this;
    }

    public function removeCritere(Criteres $critere): static
    {
        if ($this->criteres->removeElement($critere)) {
            // set the owning side to null (unless already changed)
            if ($critere->getDepartement() === $this) {
                $critere->setDepartement(null);
            }
        }

        return $this;
    }

    public function getOptCompetence(): ?int
    {
        return $this->opt_competence;
    }

    public function setOptCompetence(int $opt_competence): static
    {
        $this->opt_competence = $opt_competence;

        return $this;
    }
}
