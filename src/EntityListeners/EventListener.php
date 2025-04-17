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

    public function postLoad(Event $event): void
    {
        $now = new \DateTime();
        $statusRepo = $this->entityManager->getRepository(Status::class);

        $unMoisPasse = (clone $now)->modify('-1 month');

        if ($event->getStartDateTime() < $now && $event->getStatus()->getType() !== 'Annulée') {
            $event->setStatus($statusRepo->findOneBy(['type' => 'Passée']));
        } elseif ($event->getStartDateTime()->format('d-m-Y') === $now->format('d-m-Y') && $event->getStatus()->getType() !== 'Annulée') {
            $event->setStatus($statusRepo->findOneBy(['type' => 'En cours']));
        } elseif (($event->getStatus()->getType() !== 'Annulée') &&
            ((count($event->getUsers()) >= $event->getMaxRegistration()) ||
                ($event->getRegistrationDeadline() < $now && $now < $event->getStartDateTime()))) {
            $event->setStatus($statusRepo->findOneBy(['type' => 'Cloturée']));
        } elseif ($event->getStatus()->getType() !== 'Annulée' && $event->getStatus()->getType() !== 'En création') { // && $event->getRegistrationDeadline() <= $now) {
            $event->setStatus($statusRepo->findOneBy(['type' => 'Ouverte']));
        }
        if (($event->getStatus()->getType() === 'Passée' && $event->getStartDateTime() < $unMoisPasse) ||
            ($event->getStatus()->getType() === 'Annulée' && $event->getStartDateTime() < $unMoisPasse)) {
            $event->setStatus($statusRepo->findOneBy(['type' => 'Archivée']));
        }
        if ($event->getStatus()->getType() === 'Annulée') {
            $event->setStatus($statusRepo->findOneBy(['type' => 'Annulée']));
        }
    }
}