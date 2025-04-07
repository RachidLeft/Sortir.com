<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'isAttached')]
    private Collection $users;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'site')]
    private Collection $siteOrganizer;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->siteOrganizer = new ArrayCollection();
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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setIsAttached($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getIsAttached() === $this) {
                $user->setIsAttached(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getSiteOrganizer(): Collection
    {
        return $this->siteOrganizer;
    }

    public function addSiteOrganizer(Event $siteOrganizer): static
    {
        if (!$this->siteOrganizer->contains($siteOrganizer)) {
            $this->siteOrganizer->add($siteOrganizer);
            $siteOrganizer->setSite($this);
        }

        return $this;
    }

    public function removeSiteOrganizer(Event $siteOrganizer): static
    {
        if ($this->siteOrganizer->removeElement($siteOrganizer)) {
            // set the owning side to null (unless already changed)
            if ($siteOrganizer->getSite() === $this) {
                $siteOrganizer->setSite(null);
            }
        }

        return $this;
    }
}
