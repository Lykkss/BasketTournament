<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'equipes')]
    private Collection $joueur;

    /**
     * @var Collection<int, Game>
     */
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'equipeA')]
    private Collection $equipeB;

    /**
     * @var Collection<int, Game>
     */
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'relation')]
    private Collection $scoreEquipeA;

    public function __construct()
    {
        $this->joueur = new ArrayCollection();
        $this->equipeB = new ArrayCollection();
        $this->scoreEquipeA = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, User>
     */
    public function getJoueur(): Collection
    {
        return $this->joueur;
    }

    public function addJoueur(User $joueur): static
    {
        if (!$this->joueur->contains($joueur)) {
            $this->joueur->add($joueur);
        }

        return $this;
    }

    public function removeJoueur(User $joueur): static
    {
        $this->joueur->removeElement($joueur);

        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getEquipeB(): Collection
    {
        return $this->equipeB;
    }

    public function addEquipeB(Game $equipeB): static
    {
        if (!$this->equipeB->contains($equipeB)) {
            $this->equipeB->add($equipeB);
            $equipeB->setEquipeA($this);
        }

        return $this;
    }

    public function removeEquipeB(Game $equipeB): static
    {
        if ($this->equipeB->removeElement($equipeB)) {
            // set the owning side to null (unless already changed)
            if ($equipeB->getEquipeA() === $this) {
                $equipeB->setEquipeA(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getScoreEquipeA(): Collection
    {
        return $this->scoreEquipeA;
    }

    public function addScoreEquipeA(Game $scoreEquipeA): static
    {
        if (!$this->scoreEquipeA->contains($scoreEquipeA)) {
            $this->scoreEquipeA->add($scoreEquipeA);
            $scoreEquipeA->setRelation($this);
        }

        return $this;
    }

    public function removeScoreEquipeA(Game $scoreEquipeA): static
    {
        if ($this->scoreEquipeA->removeElement($scoreEquipeA)) {
            // set the owning side to null (unless already changed)
            if ($scoreEquipeA->getRelation() === $this) {
                $scoreEquipeA->setRelation(null);
            }
        }

        return $this;
    }
}
