<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private const EMAIL_DOMAIN = 'campus-eni.fr';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();

            // Assurer que l'email appartient toujours au domaine campus-eni.fr
            $email = sprintf('user%d@%s', $i, self::EMAIL_DOMAIN);
            $user->setEmail($email);

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);
            $user->setUsername(sprintf('user%d', $i));
            $user->setFirstname(sprintf('Firstname%d', $i));
            $user->setLastname(sprintf('Lastname%d', $i));
            $user->setPhone('0123456789');
            $user->setActive(true);
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
        }

        // Création d'un utilisateur administrateur
        $admin = new User();
        $admin->setEmail(sprintf('admin@%s', self::EMAIL_DOMAIN));
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->setUsername('admin');
        $admin->setFirstname('Admin');
        $admin->setLastname('Admin');
        $admin->setPhone('0987654321');
        $admin->setActive(true);
        $admin->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        $manager->flush();
    }
}