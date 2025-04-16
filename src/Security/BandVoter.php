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

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());

        switch ($attribute) {
            case self::VIEW:
                /**
                 * Tout utilisateur authentifié peut voir un groupe
                 */
                return true;
            case self::EDIT:
                /**
                 * Seul le propriétaire peut éditer ou supprimer
                 */
                return $user === $band->getOwner();
            case self::DELETE:
                /**
                 * Seul le propriétaire peut éditer ou supprimer
                 * L'admin peut aussi supprimer
                 */
                return $user === $band->getOwner() || $isAdmin;
        }

        return false;
    }
}