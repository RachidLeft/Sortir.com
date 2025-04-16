<?php

namespace App\Service;

use App\Repository\EventRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EventReminderService
{
    private EventRepository $eventRepository;
    private MailerInterface $mailer;

    public function __construct(EventRepository $eventRepository, MailerInterface $mailer)
    {
        $this->eventRepository = $eventRepository;
        $this->mailer = $mailer;
    }

    public function sendEventReminders(): void
    {
        $now = new \DateTime();
        $reminderDate = (clone $now)->modify('+48 hours');
        
        // Récupérer les événements qui commencent dans 48h
        $events = $this->eventRepository->findEventsStartingBetween($now, $reminderDate);

        foreach ($events as $event) {
            foreach ($event->getUsers() as $user) {
                $email = (new Email())
                    ->from('noreply@example.com')
                    ->to($user->getEmail())
                    ->subject('Rappel : Votre événement "' . $event->getName() . '" commence bientôt')
                    ->html(
                        '<p>Bonjour ' . $user->getUsername() . ',</p>
                        <p>Nous vous rappelons que l\'événement <strong>' . $event->getName() . '</strong> 
                        commencera le ' . $event->getStartDateTime()->format('d-m-Y à H:i') . '.</p>
                        <p>Cordialement,</p>
                        <p>L\'équipe.</p>'
                    );
                
                $this->mailer->send($email);
            }
        }
    }
}