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

class EventFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création de plusieurs utilisateurs
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->email)
                ->setUsername($faker->userName)
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setPhone($faker->phoneNumber)
                ->setActive(true)
                ->setPassword('password') // Remplacez par un hash si nécessaire
                ->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $users[] = $user;
        }

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
        $sites = [];
        for ($i = 0; $i < 5; $i++) {
            $site = new Site();
            $site->setName($faker->company);
            $manager->persist($site);
            $sites[] = $site;
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

        // Création des événements
        for ($i = 0; $i < 10; $i++) {
            $event = new Event();
            $startDateTime = $faker->dateTimeBetween('+3 days', '+1 month');
            $registrationDeadline = (clone $startDateTime)->modify('-2 days');

            $event->setName($faker->sentence(3))
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
