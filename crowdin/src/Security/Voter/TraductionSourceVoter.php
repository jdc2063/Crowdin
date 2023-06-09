<?php

namespace App\Security\Voter;

use App\Entity\Projet;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TraductionSourceVoter extends Voter
{
    public const ADD = 'ADD';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    protected function supports($attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::DELETE, self::ADD])
            && $subject instanceof \App\Entity\TraductionSource;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::DELETE:
                // logic to determine if the user can VIEW
                // return true or false
                break;
            case self::ADD:
                // logic to determine if the user can VIEW
                // return true or false
                break;
        }

        return false;
    }
}
