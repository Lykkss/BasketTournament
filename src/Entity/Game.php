<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\GameRepository;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Equipe $equipeA = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Equipe $equipeB = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $scoreEquipeA = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $scoreEquipeB = null; // Ajout du champ manquant

    #[ORM\ManyToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Equipe $vainqueur = null; // Ajout du champ manquant

    // 🚀 GETTERS & SETTERS (Générés automatiquement)
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipeA(): ?Equipe
    {
        return $this->equipeA;
    }

    public function setEquipeA(?Equipe $equipeA): self
    {
        $this->equipeA = $equipeA;
        return $this;
    }

    public function getEquipeB(): ?Equipe
    {
        return $this->equipeB;
    }

    public function setEquipeB(?Equipe $equipeB): self
    {
        $this->equipeB = $equipeB;
        return $this;
    }

    public function getScoreEquipeA(): ?int
    {
        return $this->scoreEquipeA;
    }

    public function setScoreEquipeA(?int $score): self
    {
        $this->scoreEquipeA = $score;
        return $this;
    }

    public function getScoreEquipeB(): ?int
    {
        return $this->scoreEquipeB;
    }

    public function setScoreEquipeB(?int $score): self
    {
        $this->scoreEquipeB = $score;
        return $this;
    }

    public function getVainqueur(): ?Equipe
    {
        return $this->vainqueur;
    }

    public function setVainqueur(?Equipe $vainqueur): self
    {
        $this->vainqueur = $vainqueur;
        return $this;
    }
}
