<?php

namespace App\Model;

use App\Entity\Site;

class Filtre
{

    private ?Site $site = null;
    private ?string $search = null;
    private ?\DateTimeInterface $startDateTime = null;
    private ?\DateTimeInterface $registrationDeadline = null;
    private ?bool $organizer = false;
    private ?bool $isRegister = false;
    private ?bool $unRegister = false;
    private ?bool $isPast = false;


    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;
        return $this;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): void
    {
        $this->search = $search;
    }

    public function getStartDateTime(): ?\DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(?\DateTimeInterface $startDateTime): void
    {
        $this->startDateTime = $startDateTime;
    }

    public function getRegistrationDeadline(): ?\DateTimeInterface
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(?\DateTimeInterface $registrationDeadline): void
    {
        $this->registrationDeadline = $registrationDeadline;
    }

    public function getOrganizer(): ?bool
    {
        return $this->organizer;
    }

    public function setOrganizer(?bool $organizer): void
    {
        $this->organizer = $organizer;
    }

    public function getIsRegister(): ?bool
    {
        return $this->isRegister;
    }

    public function setIsRegister(?bool $isRegister): void
    {
        $this->isRegister = $isRegister;
    }

    public function getUnRegister(): ?bool
    {
        return $this->unRegister;
    }

    public function setUnRegister(?bool $unRegister): void
    {
        $this->unRegister = $unRegister;
    }

    public function getIsPast(): ?bool
    {
        return $this->isPast;
    }

    public function setIsPast(?bool $isPast): void
    {
        $this->isPast = $isPast;
    }
}