<?php
namespace App\EntityListener;

use App\Entity\User;
use Doctrine\ORM\Event\PrePersistEventArgs;

class UserEmailDomainListener
{
    public function prePersist(User $user, PrePersistEventArgs $event): void
    {
        $email = $user->getEmail();
        $parts = explode('@', $email);
        if (count($parts) < 2 || trim($parts[1]) !== 'campus-eni.fr') {
            throw new \InvalidArgumentException('L\'email doit appartenir au domaine campus-eni.fr');
        }
    }
}