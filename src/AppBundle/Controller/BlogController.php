<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\Post;
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
        $posts = $em->getRepository('AppBundle\Entity\Post')->findAll();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($posts, $request->query->getInt('page', 1), 10);

        $deleteForm = [];
        foreach ($posts as $entity){
            $deleteForm[$entity->getId()] = $this->createDeleteForm($entity)->createView();
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
        return [
            'post' => $post
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
    private function createDeleteForm(Post $post)
    {

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, [
                'label' => ' ',
                'attr' => ['class' => 'btn btn-xs btn-danger fa fa-trash-o']
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
        $form = $this->createForm('AppBundle\Form\Blog\PostType', $post)
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
        $form = $this->createForm('AppBundle\Form\Blog\PostType', $post)
            ->add('Далее', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {

//            foreach ($post->getTags() as $tag){
//                $tag->addPost($post);
//            }

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
        }
        return [
            'form'=>$form->createView(),
            'post' => $post
        ];
    }
}
