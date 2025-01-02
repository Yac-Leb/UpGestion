<?php
namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $articles = [];

    #[ORM\Column(type: "datetime")]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?bool $archive = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null; // Nouvelle relation avec User

    // Constructor to set default values
    public function __construct()
    {
        // Set the current date as default for new instances
        $this->date = new \DateTime();

        // Set the default value for archive to false
        $this->archive = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticles(): array
    {
        return $this->articles;
    }

    public function setArticles(array $articles): static
    {
        $this->articles = $articles;

            return $this;
        }

        public function getDate(): ?\DateTime
        {
            return $this->date;
        }

        public function setDate(\DateTime $date): static
        {
            $this->date = $date;

            return $this;
        }

        public function isArchive(): ?bool
        {
            return $this->archive;
        }

        public function setArchive(bool $archive): static
        {
            $this->archive = $archive;

            return $this;
        }

        // Getter et setter pour la relation avec l'utilisateur
        public function getUser(): ?User
        {
            return $this->user;
        }

        public function setUser(?User $user): static
        {
            $this->user = $user;

            return $this;
        }
    }
