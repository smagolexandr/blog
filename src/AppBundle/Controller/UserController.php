<?php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ProfileController
 * @package AppBundle\Controller\User
 * @Route("/user", name="user_controller")
 */
class UserController extends Controller{
    /**
     * @Route("/", name="user_profile_page")
     * @Template("AppBundle:User:posts.html.twig")
     * @Method("GET")
     */
    public function userProfileAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle:User')->getUserProfilePosts($this->getUser()->getId());
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($posts, $request->query->getInt('page', 1), 5);


        return [
            'posts' => $pagination
        ];
    }

    /**
     * @Route("/update", name="user_update_profile")
     * @Template("AppBundle:User:update.html.twig")
     * Method("GET|POST")
     */
    public function profileHandlerAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($this->getUser()->getId());

        $form = $this->createForm('AppBundle\Form\User\UserProfileType', $user,
            [
                'action'=>$this->generateUrl('user_update_profile'),
                'method'=>'POST'
            ])
            ->add('Save', SubmitType::class, array(
                'attr'=> ['class'=> 'btn pull-right btn-primary']
            ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($user);
            $em->flush();

            $url = $this->generateUrl('user_update_profile');
            return new RedirectResponse($url);
        }
        return ['form' => $form->createView()];
    }

    /**
     * @Route("/comments", name="user_comments")
     * @Template("AppBundle:User:comments.html.twig")
     */
    public function profileCommentsAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $comments = $em->getRepository('AppBundle:User')->getUserProfileComments($this->getUser()->getId());
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($comments, $request->query->getInt('page', 1), 5);

        return ['comments' => $pagination];
    }

}