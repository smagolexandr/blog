<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Blog\Comment;
use AppBundle\Entity\Blog\Post;
use AppBundle\Entity\Blog\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SoftController
 * @package AppBundle\Admin
 * @Route("/admin/blog", name="admin_blog_controller")
 */
class BlogController extends Controller
{
    /**
     * @return array
     * @Route("/", name="admin_blog")
     * @Template("AppBundle:Admin/Blog:all.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle:Blog\Post')->getBlogPosts();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($posts, $request->query->getInt('page', 1), 15);

        return[
            'posts' => $pagination,
        ];
    }

    /**
     * @return array
     * @Route("/unapproved_posts/", name="admin_blog_unapproved_posts")
     * @Template("AppBundle:Admin/Blog:unapproved_posts.html.twig")
     */
    public function getUnapprovedPostsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle:Blog\Post')->getUnapprovedPosts();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($posts, $request->query->getInt('page', 1), 15);

        return[
            'posts' => $pagination
        ];
    }

    /**
     * @return array
     * @Route("/unapproved_comments/", name="admin_blog_unapproved_comments")
     * @Template("AppBundle:Admin/Blog:unapproved_comments.html.twig")
     */
    public function getUnapprovedCommentsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository('AppBundle:Blog\Comment')->getUnapprovedComments();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($comments, $request->query->getInt('page', 1), 15);

        return[
            'comments' => $pagination
        ];
    }

    /**
     * @return RedirectResponse
     * @Route("/approve_post/{id}", name="admin_blog_approve_post")
     */
    public function postApproveAction(Request $request,Post $post){
        $em = $this->getDoctrine()->getManager();

        $post->setApproved(true);
        $em->persist($post);
        $em->flush();

        return new RedirectResponse(
            $this->generateUrl('admin_blog_unapproved_posts')
        );
    }

    /**
     * @return RedirectResponse
     * @Route("/approve_comment/{id}", name="admin_blog_approve_comment")
     */
    public function commentApproveAction(Request $request, Comment $comment){
        $em = $this->getDoctrine()->getManager();

        $comment->setApproved(true);
        $em->persist($comment);
        $em->flush();

        return new RedirectResponse(
            $this->generateUrl('admin_blog_unapproved_comments')
        );
    }


}