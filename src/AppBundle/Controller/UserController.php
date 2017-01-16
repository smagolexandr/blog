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
     * @Route("/", name="profile_page")
     * @Template("AppBundle:User:index.html.twig")
     */
    public function singlePostAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($this->getUser()->getId());
        $form = $this->createForm('AppBundle\Form\User\UserProfileType', $user,
            [
                'action'=>$this->generateUrl('user_update_profile_handler'),
                'method'=>'POST'
            ])
            ->add('Save', SubmitType::class, array(
                'attr'=> ['class'=> 'btn btn-primary']
            ));

//        if ($request->query->get('updated')) {
//            $response['updated'] = true;
//        }

        return [
            'form'=>$form->createView()
        ];
    }

    /**
     * @Route("/prof", name="user_update_profile_handler")
     * @Template("AppBundle:User:profile.html.twig")
     * Method("POST")
     */
    public function profileHandlerAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($this->getUser()->getId());

        $form = $this->createForm('AppBundle\Form\User\UserProfileType', $user,
            [
                'action'=>$this->generateUrl('user_update_profile_handler'),
                'method'=>'POST'
            ])
            ->add('Сохранить', SubmitType::class, array(
                'attr'=> ['class'=> 'btn pull-right btn-warning']
            ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($user);
            $em->flush();

        }
        $url = $this->generateUrl('profile_page');
        return new RedirectResponse($url);
    }
}