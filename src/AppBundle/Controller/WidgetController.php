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
        $faker = Faker\Factory::create();
        $comments = [];
        for ($i=0;$i<5;$i++)
        {
            $tmp = [
                'user' => $faker->lastName,
                'comment' => $faker->sentence($nbWords = 6, $variableNbWords = true)
            ];
            array_push($comments, $tmp);
        }

        return [
            'comments' => $comments
        ];
    }
}
