<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Form\CancelEventType;
use App\Repository\EventRepository;
use App\Repository\SiteRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route(name: 'app_event_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        $cancelEventForm = [];

        foreach ($events as $event) {
            $cancelEventForm[$event->getId()] = $this->createForm(CancelEventType::class, $event, [
                'action' => $this->generateUrl('app_event_cancel', ['id' => $event->getId()]),
                'method' => 'POST',
            ])->createView();

        }

        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
            'cancelEventForm' => $cancelEventForm,
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager,
                        UserRepository $userRepository,
                        SiteRepository $siteRepository,
                        StatusRepository $statusRepository): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $newLocationData = $form->get('newLocation')->getData();

            if ($newLocationData){
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
            } else {
                $status = $statusRepository->findOneBy(['type' => 'En création']);
            }

            $event->setStatus($status);
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
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
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
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
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cancel', name: 'app_event_cancel', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(
        Request $request,
        Event $event,
        EntityManagerInterface $entityManager,
        StatusRepository $statusRepository
    ): Response {
        $form = $this->createForm(CancelEventType::class, $event, [
            'action' => $this->generateUrl('app_event_cancel', ['id' => $event->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid() && $form->get('cancel')->isClicked()) {
            $cancelStatus = $statusRepository->find(6);
            $event->setStatus($cancelStatus);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

}
