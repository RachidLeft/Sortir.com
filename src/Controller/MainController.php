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
        /**
         * Vérifier si l'utilisateur est connecté
         * Ensuite Si le paramètre 'page' n'est pas présent dans l'URL, le définir à 1
         * Enfin Nombre d'éléments par page
         */
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $limit = 6;

        /**
         * Crée une nouvelle instance de la classe Filtre
         */
        $filtre = new Filtre();

        /**
         * Vérifie si le paramètre filtre est présent dans l'URL
         * ensuite récupère tous les paramètres de filtre de l'URL
         */
        if ($request->query->has('filtre')) {
            $filtreData = $request->query->all('filtre');

            /**
             * Vérifie si le paramètre site est défini et non vide
             * ensuite récupère le site correspondant à l'ID
             * enfin, l'associe à l'objet filtre
             */
            if (isset($filtreData['site']) && $filtreData['site']) {
                $siteRepo = $entityManager->getRepository('App\Entity\Site');
                $site = $siteRepo->find($filtreData['site']);
                $filtre->setSite($site);
            }

            /**
             * Vérifie si le paramètre search est défini
             * ensuite, l'associe à l'objet filtre
             */
            if (isset($filtreData['search'])) {
                $filtre->setSearch($filtreData['search']);
            }

            /**
             * Vérifie si le paramètre startDateTime est défini et non vide
             * Définit la date et l'heure de début dans le filtre
             */
            if (isset($filtreData['startDateTime']) && $filtreData['startDateTime']) {
                $filtre->setStartDateTime(new \DateTime($filtreData['startDateTime']));
            }

            /**
             * Vérifie si le paramètre endDateTime est défini et non vide
             * Définit la date limite d'inscription dans le filtre
             */
            if (isset($filtreData['registrationDeadline']) && $filtreData['registrationDeadline']) {
                $filtre->setRegistrationDeadline(new \DateTime($filtreData['registrationDeadline']));
            }

            /**
             * Conversion explicite en booléens pour les filtres avec le paginage
             */
            $filtre->setOrganizer(isset($filtreData['organizer']) && $filtreData['organizer'] === '1');
            $filtre->setIsRegister(isset($filtreData['isRegister']) && $filtreData['isRegister'] === '1');
            $filtre->setUnRegister(isset($filtreData['unRegister']) && $filtreData['unRegister'] === '1');
            $filtre->setIsPast(isset($filtreData['isPast']) && $filtreData['isPast'] === '1');
        }

        /**
         * Créer et traiter le formulaire
         */
        $filtreForm = $this->createForm(FiltreType::class, $filtre);
        $filtreForm->handleRequest($request);

        /**
         * Utiliser le filtre (vide ou récupéré de l'URL)
         */
        $paginator = $eventRepo->findByRecherche($filtre, $user, $page, $limit);

        /**
         * Récupérer les données de pagination
         */
        $pagination = $eventRepo->getPaginationData($paginator, $page, $limit);

        // annulation d'un événement
        $cancelEventForm = [];

        /**
         * Crée un formulaire d'annulation pour chaque événement et le stocke dans le tableau
         */
        foreach ($paginator as $event) {
            $cancelEventForm[$event->getId()] = $this->createForm(CancelEventType::class, $event, [
                'action' => $this->generateUrl('app_event_cancel_redirect', ['id' => $event->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        /**
         * Récupérer le nombre total d'événements
         * Passe les événements paginés à la vue
         * Passe la vue du formulaire de filtre à la vue
         * Passe le formulaire d'annulation à la vue
         */
        return $this->render('main/index.html.twig', [
            'events' => $paginator,
            'pagination' => $pagination,
            'filtreForm' => $filtreForm->createView(),
            'cancelEventForm' => $cancelEventForm,
        ]);
    }


}
