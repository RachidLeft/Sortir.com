<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Form\EventType;
use App\Form\CancelEventType;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use App\Repository\SiteRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route(name: 'app_event_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();


        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     *Permet de créer un nouvel événement.
     * Un formulaire est généré pour collecter les données,
     * des vérifications sont effectuées pour s'assurer que les champs obligatoires sont remplis,
     * si un nouveau lieu est créé, il est vérifié s'il existe déjà dans la base de données.
     * Une fois les données validées, l'événement est enregistré dans la base de données via EntityManagerInterface.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param StatusRepository $statusRepository
     * @param LocationRepository $locationRepository
     * @return Response
     */
    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request                $request,
                        EntityManagerInterface $entityManager,
                        StatusRepository       $statusRepository,
                        LocationRepository     $locationRepository): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && ($form->get('location')->getData() || $form->get('newLocation')->getData())) {
            $newLocationData = $form->get('newLocation')->getData();

            if ($newLocationData) {
                $existingLocation = $locationRepository->findOneByNameAndAddress(
                    $newLocationData->getName(),
                    $newLocationData->getStreet(),
                    $newLocationData->getCityName()
                );

                if ($existingLocation) {
                    $this->addFlash('danger', 'Un lieu avec le même nom et la même adresse existe déjà.');
                    return $this->render('event/new.html.twig', [
                        'event' => $event,
                        'form' => $form,
                    ]);
                }

                $entityManager->persist($newLocationData);
                $entityManager->flush();
                $event->setLocation($newLocationData);
            }

            $currentUser = $this->getUser();
            $event->setOrganizer($currentUser);
            $site = $currentUser->getIsAttached();
            $event->setSite($site);

            if ($form->get('publier')->isClicked()) {
                $status = $statusRepository->findOneBy(['type' => 'Ouverte']);
                $this->addFlash('success', 'La sortie a été publiée avec succès.');
            } else {
                $status = $statusRepository->findOneBy(['type' => 'En création']);
                $this->addFlash('success', 'La sortie a été enregistrée avec succès.');
            }

            $event->setStatus($status);
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_main_index', [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash('danger', 'Veuillez remplir tous les champs obligatoires.');
        }


        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    /**
     * Affiche les détails d'un événement spécifique.
     * L'événement est récupéré via son ID et affiché dans une vue.
     *
     * @param Event $event
     * @return Response
     */
    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'location' => $event->getLocation(),
        ]);
    }

    /**
     * Permet de modifier un événement existant.
     * Un formulaire est utilisé pour mettre à jour les données de l'événement,
     * et les informations sont mises à jour dans la base de données via EntityManagerInterface.
     *
     * @param Request $request
     * @param Event $event
     * @param EntityManagerInterface $entityManager
     * @param StatusRepository $statusRepository
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager, StatusRepository $statusRepository): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newLocationData = $form->get('newLocation')->getData();

            if ($newLocationData) {
                $entityManager->persist($newLocationData);
                $entityManager->flush();
                $event->setLocation($newLocationData);
            }

            if ($form->get('publier')->isClicked()) {
                $status = $statusRepository->findOneBy(['type' => 'Ouverte']);
                $this->addFlash('success', 'La sortie a été publiée avec succès.');
            } else {
                $status = $statusRepository->findOneBy(['type' => 'En création']);
                $this->addFlash('success', 'La sortie a été modifiée avec succès.');
            }

            $event->setStatus($status);
            $entityManager->flush();

            return $this->redirectToRoute('app_main_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    /**
     * Supprime un événement.
     * Vérifie la validité d'un token CSRF avant de supprimer l'événement de la base de données.
     *
     * @param Request $request
     * @param Event $event
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_main_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Inscrit un utilisateur à un événement.
     * Vérifie que l'utilisateur n'est pas déjà inscrit avant de l'ajouter à la liste des participants.
     *
     * @param Request $request
     * @param Event $event
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/register', name: 'app_event_register', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function inscription(
        Request                $request,
        Event                  $event,
        EntityManagerInterface $entityManager,
        MailerInterface        $mailer
    ): Response
    {
        if ($this->isCsrfTokenValid('register' . $event->getId(), $request->getPayload()->getString('_token'))) {

            $user = $this->getUser();

            // Vérifier que l'utilisateur n'est pas déjà inscrit
            if ($event->getUsers()->contains($user)) {
                $this->addFlash('danger', 'Vous êtes déjà inscrit à cette sortie.');
                return $this->redirectToRoute('app_main_index');
            } else {
                $event->addUser($user);
                $entityManager->flush();
                $this->addFlash('success', 'Vous êtes maintenant inscrit à la sortie.');

                //envoi de l'email à l'utilisateur inscrit
                $email = (new Email())
                    ->from('noreply@example.com')
                    ->to($user->getEmail())
                    ->subject('Inscription à l\'évènement ' . $event->getName())
                    ->html(
                        '<p>Bonjour,</p>
                    <p>Vous êtes inscrit à l\'évènement <strong>' . $event->getName() . '</strong>.</p>
                    <p>Cordialement,</p>
                    <p>L\'équipe.</p>'
                    );
                $mailer->send($email);
            }

        }

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);

    }

    /**
     * Désinscrit un utilisateur d'un événement.
     * Vérifie que l'utilisateur est bien inscrit avant de le retirer de la liste des participants.
     * @param Request $request
     * @param Event $event
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/unregister', name: 'app_event_unregister', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function unregister(
        Request                $request,
        Event                  $event,
        EntityManagerInterface $entityManager,
        MailerInterface        $mailer): Response
    {

        if ($this->isCsrfTokenValid('unregister' . $event->getId(), $request->getPayload()->getString('_token'))) {
            $user = $this->getUser();

            // Vérifier que l'utilisateur est bien inscrit à la sortie
            if (!$event->getUsers()->contains($user)) {
                $this->addFlash('danger', 'Vous n\'êtes pas inscrit à cette sortie.');
            } else {
                $event->removeUser($user);
                $entityManager->flush();
                $this->addFlash('success', 'Votre désinscription a été prise en compte.');

                //envoi de l'email à l'utilisateur désinscrit
                $email = (new Email())
                    ->from('noreply@example.com')
                    ->to($user->getEmail())
                    ->subject('Désistement à l\'évènement ' . $event->getName())
                    ->html(
                        '<p>Bonjour,</p>
                    <p>Vous êtes désinscrit de l\'évènement <strong>' . $event->getName() . '</strong>.</p>
                    <p>Cordialement,</p>
                    <p>L\'équipe.</p>'
                    );
                $mailer->send($email);
            }
        }

        return $this->redirectToRoute('app_main_index');
    }

    /**
     * Publie un événement.
     * Vérifie la validité d'un token CSRF avant de changer le statut de l'événement.
     *
     * @param Event $event
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/event/publish/{id}', name: 'app_event_publish', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function publish(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {

        if (!$this->isCsrfTokenValid('publish' . $event->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $event->setStatus($entityManager->getRepository(Status::class)->findOneBy(['type' => 'Ouverte']));
        $entityManager->flush();
        $this->addFlash('success', 'La sortie a été publiée avec succès.');

        return $this->redirectToRoute('app_main_index');
    }

    /**
     *
     * Affiche un formulaire pour annuler un événement.
     * Ce formulaire est pré-rempli avec les données de l'événement
     * @param Event $event
     * @param EventRepository $eventRepository
     * @return Response
     */
    #[Route('/{id}/cancel', name: 'app_event_cancel', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function cancelEvent(
        Request                $request,
        Event                  $event,
        EntityManagerInterface $entityManager,
        StatusRepository       $statusRepository,
        MailerInterface        $mailer
    ): Response
    {
        // Vérification que l'utilisateur actuel est l'organisateur
        if ($event->getOrganizer() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé à annuler cet événement.');
            return $this->redirectToRoute('app_main_index');
        }

        // Vérification du statut de l'événement
        $allowedStatuses = ['Ouverte', 'Clôturée'];
        if (!in_array($event->getStatus()->getType(), $allowedStatuses)) {
            $this->addFlash('danger', 'Impossible d\'annuler un événement avec le statut ' . $event->getStatus()->getType());
            return $this->redirectToRoute('app_main_index');
        }

        // Création du formulaire
        $form = $this->createForm(CancelEventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour le statut
            $cancelStatus = $statusRepository->findOneBy(['type' => 'Annulée']);
            $event->setStatus($cancelStatus);

            // Récupérer le motif d'annulation
            $motif = $form->get('info')->getData();
            $event->setInfo($motif);

            // Persister les modifications
            $entityManager->flush();

            // Envoyer un email à l'organisateur
            $organizer = $event->getOrganizer();
            $email = (new Email())
                ->from('noreply@example.com')
                ->to($organizer->getEmail())
                ->subject('Confirmation d\'annulation de l\'événement ' . $event->getName())
                ->html(
                    '<p>Bonjour ' . $organizer->getUsername() . ',</p>
                    <p>Vous avez annulé l\'événement <strong>' . $event->getName() . '</strong> pour le motif suivant : ' . $event->getInfo() . '</p>
                    <p>Cordialement,</p>
                    <p>L\'équipe.</p>'
                );
            $mailer->send($email);

            // Envoyer un email à tous les participants
            foreach ($event->getUsers() as $user) {
                if ($user !== $organizer) { // Pour éviter d'envoyer deux fois à l'organisateur
                    $email = (new Email())
                        ->from('noreply@example.com')
                        ->to($user->getEmail())
                        ->subject('Annulation de l\'événement ' . $event->getName())
                        ->html(
                            '<p>Bonjour ' . $user->getUsername() . ',</p>
                            <p>L\'événement <strong>' . $event->getName() . '</strong> a été annulé pour le motif suivant : ' . $event->getInfo() . '</p>
                            <p>Cordialement,</p>
                            <p>L\'équipe.</p>'
                        );
                    $mailer->send($email);
                }
            }

            $this->addFlash('success', 'La sortie a été annulée avec succès.');
            return $this->redirectToRoute('app_main_index');
        }

        return $this->render('event/cancel.html.twig', [
            'cancelEventForm' => $form->createView(),
            'event' => $event,
        ]);
    }
}
