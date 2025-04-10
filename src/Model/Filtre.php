<?php

namespace App\Model;

use App\Entity\Site;

class Filtre
{
/*
    private $site;
    private $search;
    private $startDateTime;
    private $registrationDeadline;
    private $organizer;
    private $isRegister;
    private $unRegister;
    private $isPast;


    /**
     * @return mixed
     *//*
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     *//*
    public function setSite($site): void
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     *//*
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param mixed $search
     *//*
    public function setSearch($search): void
    {
        $this->search = $search;
    }

    /**
     * @return mixed
     *//*
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * @param mixed $startDateTime
     *//*
    public function setStartDateTime($startDateTime): void
    {
        $this->startDateTime = $startDateTime;
    }

    /**
     * @return mixed
     *//*
    public function getRegistrationDeadline()
    {
        return $this->registrationDeadline;
    }

    /**
     * @param mixed $registrationDeadline
     *//*
    public function setRegistrationDeadline($registrationDeadline): void
    {
        $this->registrationDeadline = $registrationDeadline;
    }

    /**
     * @return mixed
     *//*
    public function getOrganizer()
    {
        return $this->organizer;
    }

    /**
     * @param mixed $organizer
     *//*
    public function setOrganizer($organizer): void
    {
        $this->organizer = $organizer;
    }

    /**
     * @return mixed
     *//*
    public function getIsRegister()
    {
        return $this->isRegister;
    }

    /**
     * @param mixed $isRegister
     *//*
    public function setIsRegister($isRegister): void
    {
        $this->isRegister = $isRegister;
    }

    /**
     * @return mixed
     *//*
    public function getUnRegister()
    {
        return $this->unRegister;
    }

    /**
     * @return bool
     *//*
    public function setUnRegister($unRegister): void
    {
        $this->unRegister = $unRegister;
    }

    /**
     * @return mixed
     *//*
    public function getIsPast()
    {
        return $this->isPast;
    }

    /**
     * @param mixed $isPast
     *//*
    public function setIsPast($isPast): void
    {
        $this->isPast = $isPast;
    }
*/

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