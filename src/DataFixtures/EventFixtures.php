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

        // Exemple d'entités liées
        $organizer = new User();
        $organizer->setEmail('organizer@example.com')
            ->setUsername($faker->userName)
            ->setFirstName($faker->firstName)
            ->setLastName($faker->lastName)
            ->setPhone($faker->phoneNumber)
            ->setActive(true)
            ->setPassword('password')
            ->setRoles(['ROLE_USER']);
        $manager->persist($organizer);

        $location = new Location();
        $location->setLatitude($faker->latitude)
            ->setLongitude($faker->longitude)
            ->setStreet($faker->address);
        $manager->persist($location);

        $site = new Site();
        $site->setName('Site Example');
        $manager->persist($site);

        $status = new Status();
        $status->setType('Ouvert');
        $manager->persist($status);

        // Création des événements
        for ($i = 0; $i < 10; $i++) {
            $event = new Event();
            $event->setName($faker->sentence(3))
                ->setStartDateTime($faker->dateTimeBetween('+1 days', '+1 month'))
                ->setDuration($faker->numberBetween(30, 180))
                ->setRegistrationDeadline($faker->dateTimeBetween('now', '+1 week'))
                ->setMaxRegistration($faker->numberBetween(5, 50))
                ->setInfo($faker->text(200))
                ->setOrganizer($organizer)
                ->setLocation($location)
                ->setSite($site)
                ->setStatus($status);

            $manager->persist($event);
        }
        $manager->flush();
    }
}
