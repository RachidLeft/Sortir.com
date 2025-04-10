<?php

namespace App\EntityListeners;

use App\Entity\Event;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;

class EventListener
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function postLoad(Event $event)
    {
        $now = new \DateTime();
        $statusRepo = $this->entityManager->getRepository(Status::class);


        if ($event->getStartDateTime() < $now && $event->getStatus()->getType() !== 'Annulée') {
            $event->setStatus($statusRepo->findOneBy(['type' => 'Passée']));
        } elseif ($event->getStartDateTime()->format('d-m-Y') === $now->format('d-m-Y') && $event->getStatus()->getType() !== 'Annulée') {
            $event->setStatus($statusRepo->findOneBy(['type' => 'En cours']));
        } elseif ((count($event->getUsers()) >= $event->getMaxRegistration()) ||
            ($event->getRegistrationDeadline() < $now && $now < $event->getStartDateTime())) {
            $event->setStatus($statusRepo->findOneBy(['type' => 'Cloturée']));
        }
        //TODO gérer le statu Archivée
    }
}