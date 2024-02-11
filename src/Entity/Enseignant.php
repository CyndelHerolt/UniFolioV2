<?php

    namespace App\Entity;

    use App\Repository\EnseignantRepository;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\Mapping as ORM;
    use Symfony\UX\Turbo\Attribute\Broadcast;

    #[ORM\Entity(repositoryClass: EnseignantRepository::class)]
    #[Broadcast]
    class Enseignant
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        private ?int $id = null;

        #[ORM\Column(length: 50)]
        private ?string $prenom = null;

        #[ORM\Column(length: 50)]
        private ?string $nom = null;

        #[ORM\Column(length: 255, nullable: true)]
        private ?string $mail_perso = null;

        #[ORM\Column(length: 255)]
        private ?string $mail_univ = null;

        /**
         * @var Collection<int, DepartementEnseignant>
         */
        #[ORM\OneToMany(mappedBy: 'enseignant', targetEntity: DepartementEnseignant::class, cascade: ['persist', 'remove'])]
        private ?Collection $departementEnseignants;


        #[ORM\Column(length: 75)]
        private ?string $userame = null;

        #[ORM\OneToOne(mappedBy: 'enseignant', cascade: ['persist', 'remove'])]
        private ?User $user= null;

        public function __construct()
        {
            $this->departementEnseignants = new ArrayCollection();
//            $this->departements = new ArrayCollection();
        }


        public function getId(): ?int
        {
            return $this->id;
        }

        public function getPrenom(): ?string
        {
            return $this->prenom;
        }

        public function setPrenom(string $prenom): static
        {
            $this->prenom = $prenom;

            return $this;
        }

        public function getNom(): ?string
        {
            return $this->nom;
        }

        public function setNom(string $nom): static
        {
            $this->nom = $nom;

            return $this;
        }

        public function getMailPerso(): ?string
        {
            return $this->mail_perso;
        }

        public function setMailPerso(?string $mail_perso): static
        {
            $this->mail_perso = $mail_perso;

            return $this;
        }

        public function getMailUniv(): ?string
        {
            return $this->mail_univ;
        }

        public function setMailUniv(string $mail_univ): static
        {
            $this->mail_univ = $mail_univ;

            return $this;
        }

        public function addDepartementEnseignant(DepartementEnseignant $departementEnseignant): self
        {
            if (!$this->departementEnseignants->contains($departementEnseignant)) {
                $this->departementEnseignants[] = $departementEnseignant;
                $departementEnseignant->setEnseignant($this);
            }

            return $this;
        }

        public function removeDepartementEnseignant(DepartementEnseignant $departementEnseignant): self
        {
            if ($this->departementEnseignants->contains($departementEnseignant)) {
                $this->departementEnseignants->removeElement($departementEnseignant);
                // set the owning side to null (unless already changed)
                if ($departementEnseignant->getEnseignant() === $this) {
                    $departementEnseignant->setEnseignant(null);
                }
            }

            return $this;
        }

        public function addDepartement(Departement $departement): self
        {
            $departementEnseignant = new DepartementEnseignant($this, $departement);
            $departementEnseignant->setDepartement($departement);
            $this->addDepartementEnseignant($departementEnseignant);

            return $this;
        }

        public function getUsername(): ?string
        {
            return $this->username;
        }

        public function setUsername(string $userame): static
        {
            $this->username = $userame;

            return $this;
        }

        public function getUser(): ?User
        {
            return $this->user;
        }

        public function setUser(?User $user): self
        {
            // unset the owning side of the relation if necessary
            if ($user === null && $this->user !== null) {
                $this->user->setEnseignant(null);
            }

            // set the owning side of the relation if necessary
            if ($user !== null && $user->getEnseignant() !== $this) {
                $user->setEnseignant($this);
            }

            $this->user = $user;

            return $this;
        }
    }
