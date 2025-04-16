<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ChangePasswordType;
use App\Repository\UserRepository;
use App\Form\ActiveToggleType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use League\Csv\Reader;

#[Route('/user')]
final class UserController extends AbstractController{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(
        UserRepository $userRepository
    ): Response
    {   
        $users = $userRepository->findAll();
        $activeToggleForm = [];

        foreach ($users as $user) {
            $activeToggleForm[$user->getId()] = $this->createForm(ActiveToggleType::class, $user, [
                'action' => $this->generateUrl('app_user_activate_toggle', ['id' => $user->getId()]),
                'method' => 'POST',
            ])->createView();
        }
    
        return $this->render('user/index.html.twig', [
            'users' => $users,
            'activeToggleForm' => $activeToggleForm,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            // Création de l'email
            $email = (new Email())
            ->from('noreply@example.com')
            ->to($user->getEmail())
            ->subject('Votre compte a été créé')
            ->html(
                '<p>Bonjour,</p>
                <p>Votre compte a été créé avec succès.</p>
                <p>Vos identifiants :</p>
                <p>Email : ' . $user->getEmail() . '</p>
                <p>Mot de passe : ' . $plainPassword . '</p>
                <p>Cordialement,</p>
                <p>L\'équipe.</p>'
            );

            // Envoi de l'email
            $mailer->send($email);

            $this->addFlash('success', 'Inscription réussie, un email de confirmation vous a été envoyé.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(
        User $user
    ): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request, 
        User $user, 
        EntityManagerInterface $entityManager
    ): Response
    {
        $currentUser = $this->getUser();
        if(!$currentUser || $currentUser->getId() !== $user->getId()){
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que votre propre profil.');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form->get('picture')->getData();

            if($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
    
                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    
                }
    
                $user->setPicture($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit/change-password', name: 'app_user_change_password', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function changePassword(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        //verification de l'utilisateur
        $currentUser = $this->getUser();
        if(!$currentUser || $currentUser->getId() !== $user->getId()){
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que votre propre profil.');
        }

        //creation du mdp
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //récupère le nouveau mdp et l'assigne à l'utilisateur
            $newPassword = $form->get('newPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);

    }

    #[Route('/{id}/activate-toggle', name: 'app_user_activate_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function activateToggle(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(ActiveToggleType::class, $user,[
            'action' => $this->generateUrl('app_user_activate_toggle', ['id' => $user->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/register', name: 'app_user_register', methods: ['GET'])]
    public function register(): Response
    {
        return $this->render('user/register.html.twig');
    }

    #[Route('/csv_register', name: 'app_user_csv-register', methods: ['GET'])]
    public function csvRegisterGet(): Response
    {
        return $this->render('user/csvRegister.html.twig');
    }

    #[Route('/csv_register', name: 'app_user_csv_register', methods: ['POST'])]
    public function csvRegisterPost(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        // Récupération du fichier uploadé
        $csvFile = $request->files->get('csvFile');
        if (!$csvFile) {
            $this->addFlash('error', 'Aucun fichier CSV n\'a été fourni.');
            return $this->redirectToRoute('app_user_csv_register');
        }

        // Copie du fichier dans un emplacement temporaire
        $tempPath = sys_get_temp_dir() . '/' . $csvFile->getClientOriginalName();
        try {
            $csvFile->move(sys_get_temp_dir(), $csvFile->getClientOriginalName());
        } catch (FileException $e) {
            $this->addFlash('error', 'Erreur lors de l\'upload du fichier : ' . $e->getMessage());
            return $this->redirectToRoute('app_user_csv_register');
        }

        // Lecture du fichier CSV avec League\Csv
        $csv = Reader::createFromPath($tempPath, 'r');
        $csv->setHeaderOffset(0); // La première ligne contient les entêtes

        // Tableau pour conserver les utilisateurs importés
        $importedUsers = [];

        // Import des utilisateurs
        foreach ($csv->getRecords() as $record) {
            try {
                $user = new User();
                $user->setUsername($record['username']);
                $user->setFirstname($record['firstname']);
                $user->setLastname($record['lastname']);
                $user->setEmail($record['email']);
                $user->setPhone($record['phone']);
                $user->setActive(true);
                $user->setRoles(['ROLE_USER']);

                // On récupère le mot de passe en clair depuis le CSV
                $plainPassword = $record['password'];
                // Hashage du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                // On conserve l'utilisateur et son mot de passe en clair
                $importedUsers[] = ['user' => $user, 'plainPassword' => $plainPassword];
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Echec de l\'import pour l\'email ' . $record['email'] . ' : ' . $e->getMessage());
                continue;
            }
        }

        // Enregistrement en base de données
        $entityManager->flush();

        // Optionnel : suppression du fichier temporaire
        unlink($tempPath);

        // Envoi d'un email à chaque utilisateur importé
        foreach ($importedUsers as $data) {
            $user = $data['user'];
            $plainPassword = $data['plainPassword'];
            $email = (new Email())
                ->from('noreply@example.com')
                ->to($user->getEmail())
                ->subject('Votre compte a été créé')
                ->html(
                    '<p>Bonjour,</p>
                    <p>Votre compte a été créé avec succès via import CSV.</p>
                    <p>Vos identifiants :</p>
                    <p>Email : ' . $user->getEmail() . '</p>
                    <p>Mot de passe : ' . $plainPassword . '</p>
                    <p>Cordialement,</p>
                    <p>L\'équipe.</p>'
                );
            $mailer->send($email);
        }

        $this->addFlash('success', 'Utilisateurs importés avec succès.');
        return $this->redirectToRoute('app_user_index');
    }

}
