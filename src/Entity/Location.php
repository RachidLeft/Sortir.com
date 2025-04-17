<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un Nom de lieu')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le nom du lieu doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom du lieu ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Veuillez renseigner une adresse')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s]+$/',
        message: 'Votre adresse ne peut contenir que des lettres, des chiffres et des espaces'
    )]
    private ?string $street = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: 41,
        max: 52,
        notInRangeMessage: 'La latitude doit être comprise entre {{ min }} et {{ max }} pour une adresse en France.'
    )]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: -5,
        max: 10,
        notInRangeMessage: 'La longitude doit être comprise entre {{ min }} et {{ max }} pour une adresse en France.'
    )]
    private ?float $longitude = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'location', orphanRemoval: true)]
    private Collection $eventLocation;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un nom de ville')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ -]+$/',
        message: 'Le nom de ville doit uniquement contenir des lettres, des espaces, et des tirets.'
    )]
    private ?string $cityName = null;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un Code postal')]
    #[Assert\Regex(
        pattern: '/^[0-9]{5}(?:-[0-9]{4})?$/',
        message: 'Le code postal doit être composé de 5 chiffres.'
    )]
    private ?string $postalCode = null;


    public function __construct()
    {
        $this->eventLocation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEventLocation(): Collection
    {
        return $this->eventLocation;
    }

    public function addEventLocation(Event $eventLocation): static
    {
        if (!$this->eventLocation->contains($eventLocation)) {
            $this->eventLocation->add($eventLocation);
            $eventLocation->setLocation($this);
        }

        return $this;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(string $cityName): static
    {
        $this->cityName = $cityName;

        return $this;
    }

    public function removeEventLocation(Event $eventLocation): static
    {
        if ($this->eventLocation->removeElement($eventLocation)) {
            // set the owning side to null (unless already changed)
            if ($eventLocation->getLocation() === $this) {
                $eventLocation->setLocation(null);
            }
        }

        return $this;
    }


    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

}
