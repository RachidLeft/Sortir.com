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
    public function index(EventRepository $eventRepo, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $limit = 6;

        // Récupérer les paramètres de filtre de l'URL
        $filtre = new Filtre();

        // Si nous avons des paramètres de filtre dans l'URL
        if ($request->query->has('filtre')) {
            $filtreData = $request->query->all('filtre');

            // Définir les valeurs du filtre à partir de l'URL
            if (isset($filtreData['site']) && $filtreData['site']) {
                $siteRepo = $entityManager->getRepository('App\Entity\Site');
                $site = $siteRepo->find($filtreData['site']);
                $filtre->setSite($site);
            }

            // Vérifier si le filtre 'search' est actif
            if (isset($filtreData['search'])) {
                $filtre->setSearch($filtreData['search']);
            }

            //
            if (isset($filtreData['startDateTime']) && $filtreData['startDateTime']) {
                $filtre->setStartDateTime(new \DateTime($filtreData['startDateTime']));
            }

            if (isset($filtreData['registrationDeadline']) && $filtreData['registrationDeadline']) {
                $filtre->setRegistrationDeadline(new \DateTime($filtreData['registrationDeadline']));
            }

            // Conversion explicite en booléens
            $filtre->setOrganizer(isset($filtreData['organizer']) && $filtreData['organizer'] === '1');
            $filtre->setIsRegister(isset($filtreData['isRegister']) && $filtreData['isRegister'] === '1');
            $filtre->setUnRegister(isset($filtreData['unRegister']) && $filtreData['unRegister'] === '1');
            $filtre->setIsPast(isset($filtreData['isPast']) && $filtreData['isPast'] === '1');
        }

        // Créer et traiter le formulaire
        $filtreForm = $this->createForm(FiltreType::class, $filtre);
        $filtreForm->handleRequest($request);

        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            // Formulaire soumis directement, utiliser ses valeurs
            $paginator = $eventRepo->findByRecherche($filtre, $user, $page, $limit);
        } else {
            // Utiliser le filtre (vide ou récupéré de l'URL)
            $paginator = $eventRepo->findByRecherche($filtre, $user, $page, $limit);
        }

        // Récupérer les données de pagination
        $pagination = $eventRepo->getPaginationData($paginator, $page, $limit);

        // annulation d'un événement
        $cancelEventForm = [];

        // Créer le formulaire d'annulation pour chaque événement
        foreach ($paginator as $event) {
            $cancelEventForm[$event->getId()] = $this->createForm(CancelEventType::class, $event, [
                'action' => $this->generateUrl('app_event_cancel_redirect', ['id' => $event->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('main/index.html.twig', [
            'events' => $paginator,
            'pagination' => $pagination,
            'filtreForm' => $filtreForm->createView(),
            'cancelEventForm' => $cancelEventForm,
        ]);
    }


}
