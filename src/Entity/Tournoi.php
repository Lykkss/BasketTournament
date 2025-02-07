<?php

namespace App\Entity;

use App\Repository\TournoiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Game;
use App\Entity\Equipe;

#[ORM\Entity(repositoryClass: TournoiRepository::class)]
class Tournoi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type:"integer")]
    private ?int $nbMaxEquipes = 4;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'tournoisInscrits')]
    private Collection $participants;

    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'tournoi', orphanRemoval: true)]
    private Collection $games;

    // 🔥 Relation avec un tournoi final
    #[ORM\ManyToOne(targetEntity: Tournoi::class, inversedBy: 'sousTournois')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tournoi $parentTournoi = null;

    #[ORM\OneToMany(targetEntity: Tournoi::class, mappedBy: 'parentTournoi')]
    private Collection $sousTournois;

    // 🔥 Relation avec les équipes
    #[ORM\OneToMany(targetEntity: Equipe::class, mappedBy: 'tournoi', cascade: ['persist', 'remove'])]
    private Collection $equipes;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->games = new ArrayCollection();
        $this->sousTournois = new ArrayCollection();
        $this->equipes = new ArrayCollection(); // ✅ Correction pour éviter l'erreur de mapping
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

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getNbMaxEquipes(): ?int
    {
        return $this->nbMaxEquipes;
    }

    public function setNbMaxEquipes(int $nbMaxEquipes): static
    {
        $this->nbMaxEquipes = $nbMaxEquipes;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $user): self
    {
        if (!$this->participants->contains($user)) {
            $this->participants->add($user);
            $user->addTournoiInscrit($this);
        }
        return $this;
    }

    public function removeParticipant(User $user): self
    {
        if ($this->participants->removeElement($user)) {
            $user->removeTournoiInscrit($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->setTournoi($this);
        }
        return $this;
    }

    public function removeGame(Game $game): static
    {
        if ($this->games->removeElement($game)) {
            if ($game->getTournoi() === $this) {
                $game->setTournoi(null);
            }
        }
        return $this;
    }

    /**
     * 🔥 Gestion du tournoi final
     */
    public function getParentTournoi(): ?Tournoi
    {
        return $this->parentTournoi;
    }

    public function setParentTournoi(?Tournoi $parentTournoi): static
    {
        $this->parentTournoi = $parentTournoi;
        return $this;
    }

    public function getSousTournois(): Collection
    {
        return $this->sousTournois;
    }

    /**
     * 🔥 Gestion des équipes du tournoi
     */
    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    public function addEquipe(Equipe $equipe): static
    {
        if (!$this->equipes->contains($equipe)) {
            $this->equipes->add($equipe);
            $equipe->setTournoi($this);
        }
        return $this;
    }

    public function removeEquipe(Equipe $equipe): static
    {
        if ($this->equipes->removeElement($equipe)) {
            if ($equipe->getTournoi() === $this) {
                $equipe->setTournoi(null);
            }
        }
        return $this;
    }

    public function getChampionFinal(): ?Equipe
    {
        if ($this->parentTournoi === null) {
            return null; // Pas encore de tournoi final
        }

        // 🔥 Récupère le dernier match du tournoi final
        $dernierMatch = $this->games->last();
        if ($dernierMatch && $dernierMatch->getVainqueur()) {
            return $dernierMatch->getVainqueur();
        }

        return null;
    }
}
