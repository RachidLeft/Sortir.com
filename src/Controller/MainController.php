<?php

namespace App\Controller;

use App\Form\CancelEventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Model\Filtre;
use App\Form\FiltreType;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main_index')]
    #[IsGranted('ROLE_USER')]
    public function index(EventRepository $eventRepo, Request $request): Response
    {
        $user = $this->getUser();

        // Le filtre est initialisé vide
        $filtre = new Filtre();
        $filtreForm = $this->createForm(FiltreType::class, $filtre);
        $filtreForm->handleRequest($request);

        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $events = $eventRepo->findByRecherche($filtre, $user);
        } else {
            // Récupération des événements non filtrés
            $events = $eventRepo->findByRecherche(new Filtre(), $user);
        }

        // annulation d'un événement
        $cancelEventForm = [];

        foreach ($events as $event) {
            $cancelEventForm[$event->getId()] = $this->createForm(CancelEventType::class, $event, [
                'action' => $this->generateUrl('app_event_cancel_redirect', ['id' => $event->getId()]),
                'method' => 'POST',
            ])->createView();

        }

        // Les statuts seront automatiquement mis à jour par l'EventListener
        return $this->render('main/index.html.twig', [
            'events' => $events,
            'filtreForm' => $filtreForm->createView(),
            'cancelEventForm' => $cancelEventForm,
        ]);
    }


}
