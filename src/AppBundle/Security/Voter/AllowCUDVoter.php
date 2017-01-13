<?php

namespace AppBundle\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\Blog\Post;

class AllowCUDVoter extends Voter {
    const VIEW = 'VIEW';
    const EDIT = 'EDIT';

    public function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Post) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $post, TokenInterface $token)
    {
        if ($attribute === self::VIEW && !$post->isPrivate()) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::EDIT && $user->getId() === $post->getUser()->getId()) {
            return true;
        }
        return false;
    }
}
