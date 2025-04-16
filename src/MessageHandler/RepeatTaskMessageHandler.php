<?php

namespace App\MessageHandler;

use App\Message\RepeatTaskMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RepeatTaskMessageHandler{
    public function __invoke(RepeatTaskMessage $message): void
    {
        //définir l'intervalle de temps
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
