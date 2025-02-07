<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: 'gamesEquipeA')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipe $equipeA = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: 'gamesEquipeB')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipe $equipeB = null;

    #[ORM\Column(nullable: true)]
    private ?int $scoreEquipeA = null;

    #[ORM\Column(nullable: true)]
    private ?int $scoreEquipeB = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Equipe $vainqueur = null;

    #[ORM\ManyToOne(targetEntity: Tournoi::class, inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tournoi $tournoi = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipeA(): ?Equipe
    {
        return $this->equipeA;
    }

    public function setEquipeA(?Equipe $equipeA): static
    {
        $this->equipeA = $equipeA;
        return $this;
    }

    public function getEquipeB(): ?Equipe
    {
        return $this->equipeB;
    }

    public function setEquipeB(?Equipe $equipeB): static
    {
        $this->equipeB = $equipeB;
        return $this;
    }

    public function getScoreEquipeA(): ?int
    {
        return $this->scoreEquipeA;
    }

    public function setScoreEquipeA(?int $scoreEquipeA): static
    {
        $this->scoreEquipeA = $scoreEquipeA;
        $this->determineVainqueur();  // Appeler la méthode pour mettre à jour le vainqueur
        return $this;
    }

    public function getScoreEquipeB(): ?int
    {
        return $this->scoreEquipeB;
    }

    public function setScoreEquipeB(?int $scoreEquipeB): static
    {
        $this->scoreEquipeB = $scoreEquipeB;
        $this->determineVainqueur();  // Appeler la méthode pour mettre à jour le vainqueur
        return $this;
    }

    public function getVainqueur(): ?Equipe
    {
        return $this->vainqueur;
    }

    public function setVainqueur(?Equipe $vainqueur): static
    {
        $this->vainqueur = $vainqueur;
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
     * 🔥 Détermine automatiquement le vainqueur du match.
     * Appelée lorsque les scores sont modifiés pour déterminer l'équipe gagnante.
     */
    public function determineVainqueur(): void
    {
        if ($this->scoreEquipeA !== null && $this->scoreEquipeB !== null) {
            if ($this->scoreEquipeA > $this->scoreEquipeB) {
                $this->vainqueur = $this->equipeA;
            } elseif ($this->scoreEquipeB > $this->scoreEquipeA) {
                $this->vainqueur = $this->equipeB;
            } else {
                $this->vainqueur = null; // Match nul
            }
        }
    }
}
