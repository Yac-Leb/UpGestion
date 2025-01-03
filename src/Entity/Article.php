<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $premiereImage = null;

    #[ORM\Column(length: 255)]
    private ?string $deuxiemeImage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPremiereImage(): ?string
    {
        return $this->premiereImage;
    }

    public function setPremiereImage(string $premiereImage): static
    {
        $this->premiereImage = $premiereImage;

        return $this;
    }

    public function getDeuxiemeImage(): ?string
    {
        return $this->deuxiemeImage;
    }

    public function setDeuxiemeImage(string $deuxiemeImage): static
    {
        $this->deuxiemeImage = $deuxiemeImage;

        return $this;
    }
}
