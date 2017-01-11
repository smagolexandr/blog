<?php
namespace AppBundle\Services;
use AppBundle\Entity\User;
use AppBundle\Entity\Post;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PopularPosts
{
   public function getPopularPosts($em){
        $count = 5;
        $posts = $em->getRepository('AppBundle:Post')->findBy(
            array(),
            array('views' => 'DESC'),
            $count
        );
        return $posts;
    }
}