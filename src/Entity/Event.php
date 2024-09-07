<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The name should not be blank.')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The description should not be blank.')]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The location should not be blank.')]
    private ?string $emplacement = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'The number of places should not be blank.')]
    #[Assert\Type(type: 'integer', message: 'The number of places should be an integer.')]
    #[Assert\GreaterThan(value: 0, message: 'The number of places must be greater than 0.')]
    private ?int $nombre_places = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'The date should not be blank.')]
    #[Assert\Type(type: \DateTimeInterface::class, message: 'The date should be a valid date.')]
    private ?\DateTimeInterface $date = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(string $emplacement): static
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    public function getNombrePlaces(): ?int
    {
        return $this->nombre_places;
    }

    public function setNombrePlaces(int $nombre_places): static
    {
        $this->nombre_places = $nombre_places;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getGoogleMapsLink(): string
    {
        $baseUrl = "https://www.google.com/maps/search/?api=1&query=";
        $query = urlencode($this->getEmplacement());
    
        return $baseUrl . $query;
    }
}
