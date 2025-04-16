<?php

namespace App\Command;

use App\Repository\EventRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendEventRemindersCommand extends Command
{
    protected static $defaultName = 'app:send-event-reminders';

    private EventRepository $eventRepository;
    private MailerInterface $mailer;

    public function __construct(EventRepository $eventRepository, MailerInterface $mailer)
    {
        parent::__construct();
        $this->eventRepository = $eventRepository;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this->setDescription('Envoie des emails de rappel aux participants 48h avant le début de l\'événement');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();
        $reminderDate = (clone $now)->modify('+48 hours');

        $events = $this->eventRepository->findEventsStartingBetween($now, $reminderDate);

        foreach ($events as $event) {
            foreach ($event->getUsers() as $user) {
                $email = (new Email())
                    ->from('noreply@example.com')
                    ->to($user->getEmail())
                    ->subject('Rappel : Votre événement "' . $event->getName() . '" débute bientôt')
                    ->html(
                        '<p>Bonjour ' . $user->getUsername() . ',</p>
                        <p>Nous vous rappelons que l\'événement <strong>' . $event->getName() . '</strong> débutera le ' . $event->getStartDateTime()->format('d-m-Y H:i') . '.</p>
                        <p>Cordialement,</p>
                        <p>L\'équipe.</p>'
                    );
                $this->mailer->send($email);
            }
        }

        $io->success('Les rappels ont été envoyés avec succès.');
        return Command::SUCCESS;
    }
}