<?php

namespace AppBundle\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\Blog\Post;
use AppBundle\Entity\Blog\Comment;

class AllowCUDVoter extends Voter {
    const VIEW = 'VIEW';
    const EDIT = 'EDIT';

    public function supports($attribute, $entity)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$entity instanceof Post && !$entity instanceof Comment) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $entity, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($attribute === self::VIEW AND $entity->getApproved() OR $user instanceof User AND $user->getId() === $entity->getUser()->getId()) {
            return true;
        }

        if (!$user instanceof User) {
            return false;
        }

       if ($entity instanceof Post && $attribute === self::EDIT && $user->getId() === $entity->getUser()->getId() || $user->hasRole('ROLE_ADMIN')){
           return true;
       }

        if ($entity instanceof Comment && $attribute === self::EDIT && $user->getId() === $entity->getUser()->getId() || $user->hasRole('ROLE_ADMIN')){
            return true;
        }

        return false;
    }
}
