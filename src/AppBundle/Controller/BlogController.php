<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Faker;

class BlogController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        $faker = Faker\Factory::create();
        $posts = [];
        for ($i=0;$i<10;$i++)
        {
            $tmp = [
                'image' => $faker->imageUrl($width = 640, $height = 480),
                'title' => $faker->sentence($nbWords = 6, $variableNbWords = true),
                'description' => $faker-> paragraph($nbSentences = 3, $variableNbSentences = true)
            ];
            array_push($posts, $tmp);
        }

        return [
            'posts' => $posts
        ];
    }

    /**
     * @Route("/post/1", name="single_post")
     * @Template()
     */
    public function singlePostAction()
    {
        $faker = Faker\Factory::create();
        $post = [
            'image' => $faker->imageUrl($width = 640, $height = 480),
            'title' => $faker->sentence($nbWords = 6, $variableNbWords = true),
            'description' => $faker-> paragraph($nbSentences = 50, $variableNbSentences = true)
        ];
        return [
            'post' => $post
        ];
    }
}
