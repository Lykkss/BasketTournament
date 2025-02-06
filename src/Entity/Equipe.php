<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Game;
use App\Entity\Tournoi;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'equipes')]
    #[ORM\JoinTable(name: 'equipe_user')]
    private Collection $joueurs;

    #[ORM\ManyToOne(targetEntity: Tournoi::class, inversedBy: 'equipes')]
    #[ORM\JoinColumn(nullable: false)] // ✅ Une équipe DOIT être associée à un tournoi
    private ?Tournoi $tournoi = null;

    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'equipeA', cascade: ['persist', 'remove'])]
    private Collection $gamesEquipeA;

    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'equipeB', cascade: ['persist', 'remove'])]
    private Collection $gamesEquipeB;

    public function __construct()
    {
        $this->joueurs = new ArrayCollection();
        $this->gamesEquipeA = new ArrayCollection();
        $this->gamesEquipeB = new ArrayCollection();
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

    public function getTournoi(): ?Tournoi
    {
        return $this->tournoi;
    }

    public function setTournoi(?Tournoi $tournoi): static
    {
        $this->tournoi = $tournoi;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getJoueurs(): Collection
    {
        return $this->joueurs;
    }

    public function addJoueur(User $joueur): static
    {
        if (!$this->joueurs->contains($joueur)) {
            $this->joueurs->add($joueur);
            $joueur->addEquipe($this);
        }
        return $this;
    }

    public function removeJoueur(User $joueur): static
    {
        if ($this->joueurs->removeElement($joueur)) {
            $joueur->removeEquipe($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGamesEquipeA(): Collection
    {
        return $this->gamesEquipeA;
    }

    public function addGameEquipeA(Game $game): static
    {
        if (!$this->gamesEquipeA->contains($game)) {
            $this->gamesEquipeA->add($game);
            $game->setEquipeA($this);
        }
        return $this;
    }

    public function removeGameEquipeA(Game $game): static
    {
        if ($this->gamesEquipeA->removeElement($game)) {
            if ($game->getEquipeA() === $this) {
                $game->setEquipeA(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGamesEquipeB(): Collection
    {
        return $this->gamesEquipeB;
    }

    public function addGameEquipeB(Game $game): static
    {
        if (!$this->gamesEquipeB->contains($game)) {
            $this->gamesEquipeB->add($game);
            $game->setEquipeB($this);
        }
        return $this;
    }

    public function removeGameEquipeB(Game $game): static
    {
        if ($this->gamesEquipeB->removeElement($game)) {
            if ($game->getEquipeB() === $this) {
                $game->setEquipeB(null);
            }
        }
        return $this;
    }

    /**
     * Permet d'afficher le nom de l'équipe quand on affiche un objet Equipe
     */
    public function __toString(): string
    {
        return $this->nom;
    }
}
