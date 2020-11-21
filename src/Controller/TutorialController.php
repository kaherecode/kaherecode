<?php

namespace App\Controller;

use App\Entity\Tutorial;
use App\Form\TutorialType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function show($slug)
    {
        return $this->render('tutorial/show.html.twig');
    }

    /**
     * @Route("/tutorials/new", name="new_tutorial")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $tutorial = new Tutorial();
        /**
         * @var FormInterface
         */
        $form = $this->createForm(TutorialType::class, $tutorial);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tutorial);
            $em->flush();
        }

        return $this->render(
            'tutorial/new_tutorial.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/tag/{slug}", name="tag_tutorials")
     */
    public function tutorialsByTag($slug)
    {
        return $this->render('tutorial/tag_tutorials.html.twig');
    }
}
