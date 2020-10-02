<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CoreController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('core/index.html.twig');
    }

    /**
     * @Route("/tutorial/{slug}", name="tutorial_view")
     */
    public function tutorialView($slug)
    {
        return $this->render('core/tutorial_view.html.twig');
    }

    /**
     * @Route("/tag/{slug}", name="tag_tutorials")
     */
    public function tutorialsByTag($slug)
    {
        return $this->render('core/tag_tutorials.html.twig');
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile()
    {
        return $this->render('core/profile.html.twig');
    }

    /**
     * @Route("/profile/edit", name="edit_profile")
     */
    public function editProfile()
    {
        return $this->render('core/edit_profile.html.twig');
    }

    /**
     * @Route("/new-tutorial", name="new_tutorial")
     */
    public function newTutorial()
    {
        return $this->render('core/new_tutorial.html.twig');
    }
}
