<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TutorialController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('tutorial/index.html.twig');
    }

    /**
     * @Route("/tutorial/{slug}", name="tutorial_view")
     */
    public function tutorialView($slug)
    {
        return $this->render('tutorial/tutorial_view.html.twig');
    }

    /**
     * @Route("/tag/{slug}", name="tag_tutorials")
     */
    public function tutorialsByTag($slug)
    {
        return $this->render('tutorial/tag_tutorials.html.twig');
    }

    /**
     * @Route("/new-tutorial", name="new_tutorial")
     */
    public function newTutorial()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('tutorial/new_tutorial.html.twig');
    }
}
