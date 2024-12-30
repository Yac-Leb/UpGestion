<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $mail = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;
    
    #[ORM\Column(type:"array")]
    private array $roles = [];  // Tableau des rôles

    // Méthode pour obtenir les rôles de l'utilisateur
    public function getRoles(): array
    {
        // Retourner les rôles de l'utilisateur, en ajoutant un rôle par défaut 'ROLE_USER' si aucun autre rôle n'est défini
        return array_merge($this->roles, ['ROLE_USER']);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    // Méthode pour obtenir l'ID
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter et setter pour le mail
    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;
        return $this;
    }

    // Getter et setter pour le nom
    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    // Getter et setter pour le prénom
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    // Getter et setter pour le mot de passe
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    // Méthode obligatoire de PasswordAuthenticatedUserInterface
    public function getSalt(): ?string
    {
        return null; // Aucune gestion de sel pour bcrypt/argon2
    }

    // Implémentation de UserInterface
    public function eraseCredentials(): void
    {
        // On peut laisser vide si on ne gère pas de données sensibles autres que le mot de passe
    }

    // Méthode obligatoire de UserInterface pour identifier l'utilisateur
    public function getUserIdentifier(): string
    {
        return $this->mail; // Utilisation de l'email comme identifiant unique
    }
}
