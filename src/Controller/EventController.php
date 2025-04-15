<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Form\EventType;
use App\Form\CancelEventType;
use App\Repository\EventRepository;
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

#[Route('/event')]

final class EventController extends AbstractController
{
    #[Route(name: 'app_event_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(EventRepository $eventRepository) : Response
    {
        $events = $eventRepository->findAll();


        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request          $request,
                        EntityManagerInterface $entityManager,
                        StatusRepository $statusRepository): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && ($form->get('location')->getData() || $form->get('newLocation')->getData())) {
            $newLocationData = $form->get('newLocation')->getData();

            if ($newLocationData) {
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

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'location' => $event->getLocation(),
        ]);
    }

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

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_main_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/register', name: 'app_event_register', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function inscription(Request $request, Event $event, EntityManagerInterface $entityManager): Response
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
            }

        }

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);

    }

    #[Route('/{id}/unregister', name: 'app_event_unregister', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function unregister(Request $request, Event $event, EntityManagerInterface $entityManager): Response
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
            }
        }

        return $this->redirectToRoute('app_main_index');
    }

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

    #[Route('/{id}/cancel', name: 'app_event_cancel_redirect', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function cancelRedirect(
        Request $request,
        Event $event,
        EntityManagerInterface $entityManager,
        StatusRepository $statusRepository,
        EventRepository $eventRepository
    ): Response {
        $events = $eventRepository->findAll();

        // Créer le formulaire CancelEventType
        $form = $this->createForm(CancelEventType::class, $event, [
            'action' => $this->generateUrl('app_event_cancel_submit', ['id' => $event->getId()]),
            'method' => 'POST',
        ]);

        return $this->render('event/cancel.html.twig', [
            'cancelEventForm' => $form->createView(),
            'event' => $event,
        ]);
    }

#[Route('/{id}/cancel', name: 'app_event_cancel_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
#[IsGranted('ROLE_USER')]
public function cancelSubmit(
    Request $request,
    Event $event, // Cet event provient de l'URL (ex : id 7)
    EntityManagerInterface $entityManager,
    StatusRepository $statusRepository,
    EventRepository $eventRepository
): Response {
    $events = $eventRepository->findAll();

    $cancelForms = [];
    foreach ($events as $evt) { // utilisation d'une variable différente pour la boucle
        if ($this->getUser()->getId() === $evt->getOrganizer()->getId() && $evt->getStatus()->getId() === 2) {
            $cancelForms[$evt->getId()] = $this->createForm(CancelEventType::class, $evt, [
                'action' => $this->generateUrl('app_event_cancel_submit', ['id' => $evt->getId()]),
                'method' => 'POST',
            ])->createView();
        }
    }

    $form = $this->createForm(CancelEventType::class, $event, [
        'action' => $this->generateUrl('app_event_cancel_submit', ['id' => $event->getId()]),
        'method' => 'POST',
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid() && $form->get('cancel')->isClicked()) {
        $cancelStatus = $statusRepository->find(6);
        $event->setStatus($cancelStatus);
        $motif = $form->get('motif')->getData();
        $event->setInfo($event->getInfo() . " annulé : " . $motif);
        $entityManager->flush();
        $this->addFlash('success', 'La sortie a été annulée avec succès.');
    }

    return $this->redirectToRoute('app_main_index', [], Response::HTTP_SEE_OTHER);
}

}
