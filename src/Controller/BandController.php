<?php

namespace App\Controller;

use App\Entity\Band;
use App\Form\BandType;
use App\Repository\BandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/band')]
#[IsGranted('ROLE_USER')]
class BandController extends AbstractController
{
    #[Route('/', name: 'app_band_index', methods: ['GET'])]
    public function index(BandRepository $bandRepository): Response
    {
        /**
         * Récupérer tous les groupes au lieu de seulement ceux de l'utilisateur
         * Et récupère l'utilisateur connecté
         */
        $bands = $bandRepository->findAll();
        $user = $this->getUser();

        return $this->render('band/index.html.twig', [
            'bands' => $bands,
            'owner' => $user,
        ]);
    }

    #[Route('/user/{id}', name: 'app_band_user', methods: ['GET'])]
    public function userBands(int $id, BandRepository $bandRepository, EntityManagerInterface $entityManager): Response
    {
        /**
         * Récupérer l'utilisateur correspondant à l'ID donnée
         */
        $user = $entityManager->getRepository('App\Entity\User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        /**
         * Récupérer tous les groupes appartenant à cet utilisateur
         */
        $bands = $bandRepository->findBy(['owner' => $user]);

        return $this->render('band/index.html.twig', [
            'bands' => $bands,
            'owner' => $user,
            'title' => 'Groupes privés de ' . $user->getFirstname() . ' ' . $user->getLastname()
        ]);
    }

    #[Route('/new', name: 'app_band_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        /**
         * Créer un nouveau groupe
         * Récupérer l'utilisateur connecté
         * Créer un nouveau groupe et le lier à l'utilisateur
         */
        $band = new Band();
        $user = $this->getUser();
        $band->setOwner($user);

        /**
         * Crée un formulaire pour l'entité Band en utilisant le formulaire type BandType
         * Traite la requête HTTP pour remplir le formulaire avec les données soumises
         */
        $form = $this->createForm(BandType::class, $band);
        $form->handleRequest($request);

        /**
         * Vérifie si le formulaire a été soumis et est valide
         * Si oui, persiste le groupe dans la base de données
         * Ajoute un message flash de succès
         * Redirige vers la liste des groupes de l'utilisateur connecté
         */
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($band);
            $entityManager->flush();

            $this->addFlash('success', 'Votre groupe a été créé avec succès.');

            return $this->redirectToRoute('app_band_user', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('band/new.html.twig', [
            'band' => $band,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_band_show', methods: ['GET'])]
    public function show(Band $band): Response
    {
        return $this->render('band/show.html.twig', [
            'band' => $band,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_band_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Band $band, EntityManagerInterface $entityManager): Response
    {
        /**
         * Vérifie si l'utilisateur a le droit d'éditer ce groupe.
         * Si ce n'est pas le cas, une exception d'accès refusé est lancée
         */
        $this->denyAccessUnlessGranted('edit', $band);

        /**
         * Crée un formulaire pour l'entité Band en utilisant le formulaire type BandType
         * Traite la requête HTTP pour remplir le formulaire avec les données soumises
         */
        $form = $this->createForm(BandType::class, $band);
        $form->handleRequest($request);

        /**
         * Vérifie si le formulaire a été soumis et est valide
         * Si oui, met à jour le groupe dans la base de données
         * Ajoute un message flash de succès
         * Redirige vers la page
         */
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le groupe a été modifié avec succès');

            $userId = $this->getUser();
            return $this->redirectToRoute('app_band_user', ['id' => $userId->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('band/edit.html.twig', [
            'band' => $band,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_band_delete', methods: ['POST'])]
    public function delete(Request $request, Band $band, EntityManagerInterface $entityManager): Response
    {
        /**
         * Utiliser le voter au lieu de la vérification directe
         * Récupère l'utilisateur actuellement connecté
         * Vérifie si l'utilisateur a le rôle administrateur
         */
        $this->denyAccessUnlessGranted('delete', $band);
        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());

        /**
         * Vérifie si le jeton CSRF est valide pour protéger contre les attaques CSRF
         * Prépare l'entité Band pour être supprimée de la base de données
         * Exécute les opérations préparées (suppression en base de données)
         * Ajoute un message flash de succès
         */
        if ($this->isCsrfTokenValid('delete'.$band->getId(), $request->request->get('_token'))) {
            $entityManager->remove($band);
            $entityManager->flush();
            $this->addFlash('success', 'Le groupe a été supprimé.');
        }


        /**
         * Vérifie si l'utilisateur est un administrateur et si la requête provient d'une page liée aux groupes
         */
        if ($isAdmin && $request->headers->get('referer') && str_contains($request->headers->get('referer'), '/band/')) {
            return $this->redirectToRoute('app_band_index');
        } else {
            return $this->redirectToRoute('app_band_user', ['id' => $user->getId()]);
        }
    }
}