<?php

namespace App\Entity;

use App\Repository\CriteresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CriteresRepository::class)]
class Criteres
{
    private $valeurKey;
    private $valeurValue;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'criteres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Departement $departement = null;

    #[ORM\Column]
    private array $valeurs = [];

    #[ORM\OneToMany(targetEntity: ValidationCriteres::class, mappedBy: 'critere')]
    private Collection $validationCriteres;

    public function __construct()
    {
        $this->validationCriteres = new ArrayCollection();
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

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getValeurs(): array
    {
        return $this->valeurs;
    }

    public function setValeurs(array $valeurs): static
    {
        $this->valeurs = $valeurs;

        return $this;
    }

    /**
     * @return Collection<int, ValidationCriteres>
     */
    public function getValidationCriteres(): Collection
    {
        return $this->validationCriteres;
    }

    public function addValidationCritere(ValidationCriteres $validationCritere): static
    {
        if (!$this->validationCriteres->contains($validationCritere)) {
            $this->validationCriteres->add($validationCritere);
            $validationCritere->setCritere($this);
        }

        return $this;
    }

    public function removeValidationCritere(ValidationCriteres $validationCritere): static
    {
        if ($this->validationCriteres->removeElement($validationCritere)) {
            // set the owning side to null (unless already changed)
            if ($validationCritere->getCritere() === $this) {
                $validationCritere->setCritere(null);
            }
        }

        return $this;
    }

    public function getValeurKey(): ?int
    {
        return $this->valeurKey;
    }

    public function setValeurKey(int $valeurKey): self
    {
        $this->valeurKey = $valeurKey;

        return $this;
    }

    public function getValeurValue(): ?string
    {
        return $this->valeurValue;
    }

    public function setValeurValue(string $valeurValue): self
    {
        $this->valeurValue = $valeurValue;

        return $this;
    }
}
