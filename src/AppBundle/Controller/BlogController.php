<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle\Entity\Post')->findAll();

        return [
            'posts' => $posts
        ];
    }

    /**
     * @Route("/post/{slug}", name="single_post")
     * @ParamConverter("post", class="AppBundle:Post", options={"slug" = "slug"})
     * @Template()
     */
    public function singlePostAction(Post $post, Request $request)
    {
        return [
            'post' => $post
        ];
    }
}
