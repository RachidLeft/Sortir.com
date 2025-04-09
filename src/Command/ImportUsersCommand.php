<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use League\Csv\Reader;

class ImportUsersCommand extends Command
{
    protected static $defaultName = "app:import-users";

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import users from a CSV file')
            //définition du chemin du fichier
            ->addArgument('file', InputArgument::REQUIRED, 'Import/inscription.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int{
        $io = new SymfonyStyle($input, $output);
        //récupération du chemin du fichier
        $filePath = $input->getArgument('file');

        if(!file_exists($filePath)){
            $io->error("File not found: $filePath");
            return Command::FAILURE;
        }

        //ouvre le fichier en mode lecture
        $csv = Reader::createFromPath($filePath, 'r');
        //indique que la première ligne contient le nom des colonnes
        $csv->setHeaderOffset(0);

        //ajout des utilisateurs dans la base de données
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
        
                // Hashage du mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, $record['password']);
                $user->setPassword($hashedPassword);
        
                $this->entityManager->persist($user);
                // Flush pour chaque utilisateur : requête distincte
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $io->warning('Echec de l\'import pour l\'email ' . $record['email'] . ': ' . $e->getMessage());
                // Optionnellement, on peut continuer sur les autres lignes.
                continue;
            }
        }
        return Command::SUCCESS;
    }

}