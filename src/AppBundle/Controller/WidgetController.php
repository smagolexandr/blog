<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Faker;
class WidgetController extends Controller
{
    /**
     * @Template("AppBundle:Widget:lastComments.html.twig")
     */
    public function lastCommentsAction()
    {
        return [

        ];
    }
}
