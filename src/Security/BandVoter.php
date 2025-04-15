<?php

namespace App\Security;

use App\Entity\Band;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BandVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Band;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Band $band */
        $band = $subject;

        switch ($attribute) {
            case self::VIEW:
                // Tout utilisateur authentifié peut voir un groupe
                return true;
            case self::EDIT:
            case self::DELETE:
                // Seul le propriétaire peut éditer ou supprimer
                return $user === $band->getOwner();
        }

        return false;
    }
}