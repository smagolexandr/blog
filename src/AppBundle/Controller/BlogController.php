<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Blog\Post;
use AppBundle\Entity\Blog\Comment;
use AppBundle\Entity\Blog\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $posts = $em->getRepository('AppBundle\Entity\Blog\Post')->getBlogPostsByParams($request->query);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($posts, $request->query->getInt('page', 1), 5);

        $deleteForm = [];

        foreach ($posts as $post) {
            if($this->isGranted('EDIT', $post)){
                $deleteForm[$post->getId()] = $this->createDeletePostForm($post)->createView();
            }
        }
        return [
            'posts' => $pagination,
            'deleteForm' => $deleteForm
        ];
    }

    /**
     * @Route("/post/{slug}", name="single_post")
     * @ParamConverter("post", class="AppBundle:Blog\Post", options={"slug" = "slug"})
     * @Template()
     */
    public function singlePostAction(Post $post, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $response=[];
        $deleteForm = [];
        $em = $this->getDoctrine()->getManager();

        $comment = new Comment();

        if ($user instanceof User) {
            $comment = new Comment();
            $comment->setUser($user);
            $comment->setPost($post);

            $form = $this->createForm('AppBundle\Form\Blog\CommentType', $comment,
                array(
                    'action' => $this->generateUrl('new_comment', ['slug'=>$post->getSlug()])
                ))
                ->add('submit', SubmitType::class);
            $response['newComment'] = $form->createView();
        }

        $comments = $em->getRepository('AppBundle:Blog\Comment')->getCommentsSorted($post->getId());

        foreach ($comments as $comment) {
            if ($this->isGranted('EDIT', $comment) || $this->isGranted('EDIT', $post)) {
                $deleteForm[$comment->getId()] = $this->createDeleteCommentForm($comment)->createView();
            }
        }

        $post->setViews((int)$post->getViews()+1);
        $em->persist($post);
        $em->flush();

        return $response += [
            'post' => $post,
            'deleteForm' => $deleteForm
        ];
    }

    /**
     * @param Post $post
     * @return RedirectResponse
     *
     * @Route("/delete/{id}", name="post_delete")
     * "Method("DELETE|RedirectResponse")
     */
    public function deleteAction(Post $post)
    {
        if($this->isGranted('EDIT', $post)){
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
            return $this->redirect($this->generateUrl('homepage'));
        } else{
            throw $this->createAccessDeniedException('Access denied.');
        }
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
        $user = $this->getUser();
        if ($user instanceof User) {
        $em = $this->getDoctrine()->getManager();
        $post = new Post();
        $form = $this->createForm('AppBundle\Form\Blog\PostType', $post)
            ->add('Далее', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-success center-btn')
            ));

        return ['form'=>$form->createView()];
        } else {
            throw $this->createAccessDeniedException('Access denied.');
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse|array
     * @Route("/new", name="blog_post_new_handle")
     * @Method("POST")
     */
    public function newPostHandleAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if ($user instanceof User) {
        $post = new Post();
        $post->setUser($user);
        $form = $this->createForm('AppBundle\Form\Blog\PostType', $post)
            ->add('Далее', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {

            foreach ($post->getTags() as $tag){
                $tag->addPost($post);
            }

            $em->persist($post);
            $em->flush();

            }
        $url = $this->generateUrl('homepage');
        return $this->redirect($url);
        } else {
            throw $this->createAccessDeniedException('Access denied.');
        }

    }


    /**
     * @Route("/edit/{post}", name="blog_post_edit")
     * @Template("AppBundle:Blog:edit.html.twig")
     */
    public function editPostAction(Request $request, Post $post)
    {
        $em = $this->getDoctrine()->getManager();


        if($this->isGranted('EDIT', $post)){
            $form = $this->createForm('AppBundle\Form\Blog\PostType', $post)
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
                $url = $this->generateUrl('single_post', ['slug'=>$post->getSlug()] );
                return new RedirectResponse($url);
            }
            return [
                'form' => $form->createView()
            ];
        } else {
            throw $this->createAccessDeniedException('Access denied.');
        }
    }

    /**
     *
     * @Route("/post/{slug}/new-comment", name="new_comment")
     * @ParamConverter("post", class="AppBundle:Blog\Post", options={"slug" = "slug"})
     */
    public function newCommentHandler(Request $request, Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $url = $this->generateUrl('single_post', ['slug'=>$post->getSlug()]);

        $user = $this->getUser();
        if ($user instanceof User) {
            $comment = new Comment();
            $comment->setUser($user);
            $comment->setPost($post);

            $form = $this->createForm('AppBundle\Form\Blog\CommentType', $comment,
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
     * @ParamConverter("comment", class="AppBundle:Blog\Comment", options={"id" = "id"})
     * @ Method("DELETE|RedirectResponse")
     */
    public function deleteCommentAction(Comment $comment)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $comment->getPost();

        if (!$comment) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }

        if($this->isGranted('EDIT', $comment) || $this->isGranted('EDIT', $post)) {
            $em->remove($comment);
            $em->flush();

            return $this->redirect($this->generateUrl('single_post',
                array(
                    'slug' => $post->getSlug()
                ))
            );
        } else {
            throw $this->createAccessDeniedException('Access denied.');
        }
    }

    /**
     * @Route("/tag/new", name="new_tag", options={"expose"=true})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function newTagAction(Request $request)
    {
        $tagName = $request->get('tag');
        $em = $this->getDoctrine()->getManager();

        if($tagName){
            $tag = new Tag();
            $tag->setName($tagName);
            $em->persist($tag);
            $em->flush();

            return new JsonResponse([
                'id' => $tag->getId(),
                'name'=> $tag->getName()
            ]);
        }

        return new JsonResponse('Не правильное имя тега', 401);
    }
}
