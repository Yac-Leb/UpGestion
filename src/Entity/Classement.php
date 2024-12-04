<?php

namespace App\Entity;

use App\Repository\ClassementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassementRepository::class)]
class Classement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'json')]
    private array $classement = [];

    public function __construct()
    {
        // Initialisation de la date à la création de l'entité
        $this->date = new \DateTime(); // Optionnel si vous souhaitez un `date` par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getClassement(): array
    {
        return $this->classement;
    }

    public function setClassement(array $classement): self
    {
        $this->classement = $classement;

        return $this;
    }
}
