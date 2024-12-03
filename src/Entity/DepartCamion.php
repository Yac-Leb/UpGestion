<?php

namespace App\Entity;

use App\Repository\DepartCamionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartCamionRepository::class)]
class DepartCamion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
