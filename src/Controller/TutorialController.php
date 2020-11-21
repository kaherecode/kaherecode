<?php

namespace App\Controller;

use App\Entity\Category;
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

            $categories = array_filter(
                explode(",", $form->get('categories')->getData()),
                fn ($c) => ctype_alnum(trim($c))
            );
            foreach ($categories as $cat) {
                if (trim($cat)) {
                    // check if the category already exists
                    $existingCategory = $em
                        ->getRepository(Category::class)
                        ->findOneByLabel(strtolower(trim($cat)));

                    if ($existingCategory) { // if it is, simply add it
                        $tutorial->addCategory($existingCategory);
                    } else { // if not create a new one the add it to tutorial
                        $category = new Category();
                        $category->setLabel(trim($cat));
                        $tutorial->addCategory($category);
                        $em->persist($category);
                    }
                }
            }

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
