<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'The number of places should not be blank.')]
    #[Assert\Type(type: 'integer', message: 'The number of places should be an integer.')]
    #[Assert\GreaterThan(value: 0, message: 'The number of places must be greater than 0.')]
    private ?int $nombreplaces = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreplaces(): ?int
    {
        return $this->nombreplaces;
    }

    public function setNombreplaces(int $nombreplaces): static
    {
        $this->nombreplaces = $nombreplaces;

        return $this;
    }
}
