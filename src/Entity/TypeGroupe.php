<?php

namespace App\Entity;

use App\Repository\TypeGroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeGroupeRepository::class)]
class TypeGroupe
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?bool $mutualise = false;

    #[ORM\Column]
    private ?int $ordreSemestre = null;

    #[ORM\Column(length: 2)]
    private ?string $type = null;

    #[ORM\ManyToMany(targetEntity: Semestre::class, inversedBy: 'typeGroupes')]
    private Collection $semestre;

    #[ORM\OneToMany(targetEntity: Groupe::class, mappedBy: 'typeGroupe')]
    private Collection $groupes;

    public function __construct()
    {
        $this->semestre = new ArrayCollection();
        $this->groupes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setSemestre(Collection $semestre): void
    {
        $this->semestre = $semestre;
    }

    public function setGroupes(Collection $groupes): void
    {
        $this->groupes = $groupes;
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

    public function isMutualise(): ?bool
    {
        return $this->mutualise;
    }

    public function setMutualise(bool $mutualise): static
    {
        $this->mutualise = $mutualise;

        return $this;
    }

    public function getOrdreSemestre(): ?int
    {
        return $this->ordreSemestre;
    }

    public function setOrdreSemestre(?int $ordreSemestre): static
    {
        $this->ordreSemestre = $ordreSemestre;

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

    /**
     * @return Collection<int, Semestre>
     */
    public function getSemestre(): Collection
    {
        return $this->semestre;
    }

    public function addSemestre(Semestre $semestre): static
    {
        if (!$this->semestre->contains($semestre)) {
            $this->semestre->add($semestre);
        }

        return $this;
    }

    public function removeSemestre(Semestre $semestre): static
    {
        $this->semestre->removeElement($semestre);

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
            $groupe->setTypeGroupe($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): static
    {
        if ($this->groupes->removeElement($groupe)) {
            // set the owning side to null (unless already changed)
            if ($groupe->getTypeGroupe() === $this) {
                $groupe->setTypeGroupe(null);
            }
        }

        return $this;
    }
}
