<?php

namespace App\Service;

use App\Repository\EventRepository;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SchedulerService
{
    public function __construct(
        private EventRepository $eventRepository,
        private MailerInterface $mailer
    ) {}
    
    #[AsSchedule('*/10 * * * *')] // Toutes les 10 minutes
    public function sendEventReminders(): void
    {
        $now = new \DateTime();
        $reminderDate = (clone $now)->modify('+48 hours');
        
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