<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Post;
use AppBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;



class BlogController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle\Entity\Post')->getBlogPostsByParams($request->query);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($posts, $request->query->getInt('page', 1), 5);

        $deleteForm = [];
        foreach ($posts as $entity){
            $deleteForm[$entity->getId()] = $this->createDeletePostForm($entity)->createView();
        }

        return [
            'posts' => $pagination,
            'deleteForm' => $deleteForm
        ];
    }

    /**
     * @Route("/post/{slug}", name="single_post")
     * @ParamConverter("post", class="AppBundle:Post", options={"slug" = "slug"})
     * @Template()
     */
    public function singlePostAction(Post $post, Request $request)
    {
        //dump($request->query);
        $deleteForm = [];
        $em = $this->getDoctrine()->getManager();
        $post->setViews((int)$post->getViews()+1);
        $em->persist($post);
        $em->flush();

        $comment = new Comment();

        $comment->setPost($post);
        $form = $this->createForm('AppBundle\Form\CommentType', $comment,
            array(
                'action' => $this->generateUrl('new_comment', ['slug'=>$post->getSlug()])
            ))
            ->add('submit', SubmitType::class);

        $comments = $em->getRepository('AppBundle:Comment')->getCommentsSorted($post->getId());

        foreach ($comments as $comment) {
                $deleteForm[$comment->getId()] = $this->createDeleteCommentForm($comment)->createView();
        }

        return [
            'post' => $post,
            'newComment' =>  $form->createView(),
            'deleteForm' => $deleteForm
        ];
    }

    /**
     * @param Post $post
     * @return RedirectResponse
     *
     * @Route("/delete/{id}", name="post_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Creates a form to delete a User entity.
     * @param Post $post The User entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeletePostForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, [
                'label' => ' ',
                'attr' => ['class' => 'btn btn-xs btn-danger']
            ])
            ->getForm();
    }

    /**
     * @return array
     * @Route("/new", name="blog_post_new")
     * @Template("AppBundle:Blog:new.html.twig")
     * @Method("GET")
     */
    public function newPostAction()
    {
        $em = $this->getDoctrine()->getManager();
        $post = new Post();
        $form = $this->createForm('AppBundle\Form\PostType', $post)
            ->add('Далее', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-success center-btn')
            ));

        return ['form'=>$form->createView()];
    }

    /**
     * @param Request $request
     * @return RedirectResponse|array
     * @Route("/new", name="blog_post_new_handle")
     * @Method("POST")
     */
    public function newPostHandleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $post = new Post();
        $form = $this->createForm('AppBundle\Form\PostType', $post)
            ->add('Далее', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {

            foreach ($post->getTags() as $tag){
                $tag->addPost($post);
            }

            $em->persist($post);
            $em->flush();
            $url = $this->generateUrl('homepage');
            return new RedirectResponse($url);
        }

        return ['form'=>$form->createView()];
    }


    /**
     * @Route("/{post}/edit", name="blog_post_edit")
     * @Template("AppBundle:Blog:edit.html.twig")
     */
    public function editPostAction(Request $request, Post $post)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('AppBundle\Form\PostType', $post)
            ->add('Сохранить', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-success center-btn')
            ));

        $form->handleRequest($request);

        if ($form->isValid()) {

            foreach ($post->getTags() as $tag){
                if (!$tag->getPosts()->contains($post)){
                    $tag->addPost($post);
                }
            }

            $em->persist($post);
            $em->flush();
        }
        return [
            'form'=>$form->createView(),
            'post' => $post
        ];
    }

    /**
     *
     * @Route("/post/{slug}/new-comment", name="new_comment")
     * @ParamConverter("post", class="AppBundle:Post", options={"slug" = "slug"})
     */
    public function newCommentHandler(Request $request, Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $url = $this->generateUrl('single_post', ['slug'=>$post->getSlug()]);


            $comment = new Comment();
            $comment->setUser($em->getRepository('AppBundle:User')->find(1));
            $comment->setPost($post);

            $form = $this->createForm('AppBundle\Form\CommentType', $comment,
                array(
                    'action' => $this->generateUrl('new_comment', ['slug'=>$post->getSlug()])
                ))
                ->add('submit', SubmitType::class);
            $response['newComment'] = $form->createView();

            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($comment);
                $em->flush();
            }

            $url = $url . '#comment_' . $comment->getId();


        return new RedirectResponse($url);
    }

    private function createDeleteCommentForm(Comment $comment)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('comment_delete', array('id' => $comment->getId())))
            ->add('submit', SubmitType::class, [
                'label' => "Удалить",
                'attr' => ['class' => 'btn btn-xs btn-danger']
            ])
            ->getForm();
    }


    /**
     * @param $comment
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("comment/{id}/delete", name="comment_delete")
     * @ParamConverter("comment", class="AppBundle:Comment", options={"id" = "id"})
     */
    public function deleteCommentAction(Comment $comment)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $comment->getPost();

        if (!$comment) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }
        $em->remove($comment);
        $em->flush();

        return $this->redirect($this->generateUrl('single_post',
            array(
                'slug'=>$post->getSlug()
            ))
        );

    }
}
