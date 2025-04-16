<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Site;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EventFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');


        // Création de plusieurs lieux
        $locations = [];
        for ($i = 0; $i < 5; $i++) {
            $location = new Location();
            $location->setName($faker->company)
                ->setLatitude($faker->latitude)
                ->setLongitude($faker->longitude)
                ->setStreet($faker->address)
                ->setPostalCode($faker->postcode)
                ->setCityName($faker->city);
            $manager->persist($location);
            $locations[] = $location;
        }

        // Création de plusieurs sites
        $siteNames = ['Nantes', 'Saint-Herblain', 'Rennes', 'Angers', 'Le Mans'];
        $sites = [];
        foreach ($siteNames as $siteName) {
            $site = new Site();
            $site->setName($siteName);
            $manager->persist($site);
            $sites[] = $site;
        }

        // Création de plusieurs utilisateurs
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->userName . '@campus-eni.fr')
                ->setUsername($faker->userName)
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setPhone($faker->phoneNumber)
                ->setIsAttached($site)
                ->setActive(true)
                ->setPassword($this->passwordHasher->hashPassword($user, '1234'))
                ->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $users[] = $user;
        }

        // Création de plusieurs statuts
        $statuses = [];
        $statusTypes = ['Ouverte', 'Clôturée', 'Archivée', 'En cours', 'Passée', 'En création', 'Annulée'];
        foreach ($statusTypes as $type) {
            $status = new Status();
            $status->setType($type);
            $manager->persist($status);
            $statuses[] = $status;
        }

        // Tableau de noms d'événements prédéfinis
        $eventNames = ['Balade', 'Soirée dansante', 'Vélo', 'Randonnée', 'Pique-nique', 'Atelier cuisine',
            'Visite de musée', 'Concert', 'Match de foot', 'Sortie cinéma', 'Escape game', 'Jeux de société', 'Séance de yoga',
            'Cours de danse', 'Séance de méditation', 'Sortie en bateau', 'Visite de château', 'Atelier peinture',];

        // Création des événements
        for ($i = 0; $i < 18; $i++) {
            $event = new Event();
            $startDateTime = $faker->dateTimeBetween('+3 days', '+1 month');
            $registrationDeadline = (clone $startDateTime)->modify('-2 days');

            $event->setName($faker->randomElement($eventNames)) // Choix aléatoire dans le tableau
            ->setStartDateTime($startDateTime)
                ->setDuration($faker->numberBetween(30, 180))
                ->setRegistrationDeadline($registrationDeadline)
                ->setMaxRegistration($faker->numberBetween(5, 50))
                ->setInfo($faker->text(200))
                ->setOrganizer($faker->randomElement($users))
                ->setLocation($faker->randomElement($locations))
                ->setSite($faker->randomElement($sites))
                ->setStatus($faker->randomElement($statuses));

            $manager->persist($event);
        }

        $manager->flush();
    }
}