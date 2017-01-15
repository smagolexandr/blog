<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WidgetController extends Controller
{
    /**
     * @Template("AppBundle:Widget:lastComments.html.twig")
     * @Route("/last_comments/", name="widget_last_comments")
     */
    public function lastCommentsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository('AppBundle:Blog\Comment')->getLastComments();
        return [
            "comments" => $comments
        ];
    }

    /**
     * @Template("AppBundle:Widget:popularPosts.html.twig")
     * @Route("/popular_posts/{count}", name="widget_popular_posts")
     */
    public function popularPostsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle:Blog\Post')->getPopularPosts();
        return [
            "posts" => $posts
        ];
    }
}
