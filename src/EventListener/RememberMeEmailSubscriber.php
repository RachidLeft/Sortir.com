<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class RememberMeEmailSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getAuthenticatedToken()->getUser();
        if (!is_object($user) || !method_exists($user, 'getEmail')) {
            return;
        }
        $email = $user->getEmail();

        // Récupère la réponse ou en crée une nouvelle si inexistante
        $response = $event->getResponse() ?? new Response();

        // Définit un cookie pour sauvegarder l'email pendant 30 jours
        $cookie = Cookie::create('REMEMBERME_EMAIL', $email, new \DateTime('+30 days'));
        $response->headers->setCookie($cookie);

        $event->setResponse($response);
    }
}