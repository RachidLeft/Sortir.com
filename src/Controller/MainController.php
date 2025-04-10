<?php

namespace App\Controller;

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

    // Route principale de l'application
    #[Route('/', name: 'app_main_index')]
    #[IsGranted('ROLE_USER')]
    public function index(EventRepository $eventRepo, StatusRepository $statusRepo, EntityManagerInterface $entityManager, Request $request): Response
    {
            $user = $this->getUser();

            // Récupération des événements sauf ceux archivés
            $events = $eventRepo->findByRecherche(new Filtre(), $user);

            $today = new \DateTime('now');
            $statusEnCours = $statusRepo->findOneBy(['type' => "En cours"]);
            $statusPassee = $statusRepo->findOneBy(['type' => "Passée"]);
            $statusArchivee = $statusRepo->findOneBy(['type' => "Archivée"]);
            $statusCloturee = $statusRepo->findOneBy(['type' => "Clôturée"]);
            $statusOuverte = $statusRepo->findOneBy(['type' => "Ouverte"]);
            $statusAnnulee = $statusRepo->findOneBy(['type' => "Annulée"]);
            $statusEnCreation = $statusRepo->findOneBy(['type' => "En création"]);

            foreach ($events as $event) {
                $startDate = $event->getStartDateTime();
                $duration = $event->getDuration() ?? 1;
                $endDate = (clone $startDate)->add(new \DateInterval('PT' . $duration . 'M'));
                $archiveDate = (clone $startDate)->add(new \DateInterval('P1M'));

                if ($event->getRegistrationDeadline() < $today) {
                    if ($startDate < $today) {
                        if ($endDate > $today && $event->getStatus() !== $statusEnCours) {
                            $event->setStatus($statusEnCours);
                        } elseif ($endDate < $today && $event->getStatus() !== $statusPassee) {
                            $event->setStatus($statusPassee);
                        } elseif ($archiveDate < $today && $event->getStatus() !== $statusArchivee) {
                            $event->setStatus($statusArchivee);
                        }
                    } elseif ($startDate > $today && $event->getStatus() !== $statusCloturee) {
                        $event->setStatus($statusCloturee);
                    }
                }

                $entityManager->persist($event);
            }
            $entityManager->flush();

            $filtre = new Filtre();
            $filtreForm = $this->createForm(FiltreType::class, $filtre);
            $filtreForm->handleRequest($request);

            if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
                $filteredEvents = $eventRepo->findByRecherche($filtre, $user);

                //dd($filteredEvents);
                return $this->render('main/index.html.twig', [
                    'events' => $filteredEvents,
                    'filtreForm' => $filtreForm->createView(),
                ]);
            }

            return $this->render('main/index.html.twig', [
                'events' => $events,
                'filtreForm' => $filtreForm->createView(),
            ]);

    }


    // Route pour s'inscrire à un événement
    /*#[Route('/event/register/{userId}/{eventId}', name: 'app_event_register')]
    public function registerEvent(EventRepository $eventRepo, EntityManagerInterface $entityManager, int $userId, int $eventId): Response
    {
        $today = new \DateTime('now');
        $event = $eventRepo->find($eventId);
        $user = $entityManager->getRepository(User::class)->find($userId);

        if ($today >= $event->getRegistrationDeadline()) {
            $this->addFlash('error', "Impossible de s'inscrire, la date limite d'inscription est dépassée.");
        } elseif ($event->getMaxRegistrations() > count($event->getUsers())) {
            $event->addUser($user);
            $entityManager->flush();
            $this->addFlash('success', 'Vous êtes inscrit à l\'événement !');
        } else {
            $this->addFlash('danger', 'L\'événement est complet.');
        }

        return $this->redirectToRoute('app_main_index');
    }

    // Route pour se désinscrire d'un événement
    #[Route('/event/unregister/{userId}/{eventId}', name: 'app_event_unregister')]
    public function unregisterEvent(EventRepository $eventRepo, EntityManagerInterface $entityManager, int $userId, int $eventId): Response
    {
        $event = $eventRepo->find($eventId);
        $user = $entityManager->getRepository(User::class)->find($userId);

        $event->removeUser($user);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes désinscrit de l\'événement.');
        return $this->redirectToRoute('app_main_index');
    }*/
}
