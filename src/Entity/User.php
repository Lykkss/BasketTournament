<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Equipe>
     */
    #[ORM\ManyToMany(targetEntity: Equipe::class, mappedBy: 'joueur')]
    private Collection $equipes;

    /**
     * @var Collection<int, Tournoi>
     */
    #[ORM\ManyToMany(targetEntity: Tournoi::class, inversedBy: 'participants')]
    #[ORM\JoinTable(name: 'tournoi_user')] // ✅ Vérifier que le nom de la table est correct
    private Collection $tournoisInscrits;

    public function __construct()
    {
        $this->equipes = new ArrayCollection();
        $this->tournoisInscrits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password ?: null;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    public function addEquipe(Equipe $equipe): static
    {
        if (!$this->equipes->contains($equipe)) {
            $this->equipes->add($equipe);
            $equipe->addJoueur($this);
        }
        return $this;
    }

    public function removeEquipe(Equipe $equipe): static
    {
        if ($this->equipes->removeElement($equipe)) {
            $equipe->removeJoueur($this);
        }
        return $this;
    }

    /**
 * Récupérer la liste des tournois auxquels l'utilisateur est inscrit.
 *
 * @return Collection<int, Tournoi>
 */
public function getTournoisInscrits(): Collection
{
    return $this->tournoisInscrits;
}

/**
 * Ajouter un tournoi à la liste des tournois inscrits par l'utilisateur.
 *
 * @param Tournoi $tournoi
 * @return static
 */
public function addTournoiInscrit(Tournoi $tournoi): static
{
    if (!$this->tournoisInscrits->contains($tournoi)) {
        $this->tournoisInscrits->add($tournoi);
        $tournoi->addParticipant($this); // 🔥 Ajout aussi côté Tournoi
    }
    return $this;
}

/**
 * Supprimer un tournoi de la liste des tournois inscrits par l'utilisateur.
 *
 * @param Tournoi $tournoi
 * @return static
 */
public function removeTournoiInscrit(Tournoi $tournoi): static
{
    if ($this->tournoisInscrits->removeElement($tournoi)) {
        $tournoi->removeParticipant($this); // 🔥 Suppression aussi côté Tournoi
    }
    return $this;
}

}
