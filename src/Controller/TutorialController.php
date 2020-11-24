<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Tutorial;
use App\Form\TutorialType;
use App\Service\CloudinaryService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
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
     * @Route("/tag/{slug}", name="tag_tutorials")
     */
    public function tutorialsByTag($slug)
    {
        return $this->render('tutorial/tag_tutorials.html.twig');
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
    public function create(
        Request $request,
        SluggerInterface $slugger,
        CloudinaryService $cloudinary
    ) {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $tutorial = new Tutorial();
        /**
         * @var FormInterface
         */
        $form = $this->createForm(TutorialType::class, $tutorial);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // set the tutorial categories
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

            // upload picture to cloudinary
            $picture = $form->get("picture")->getData();
            if ($picture) {
                $upload = $cloudinary->upload(
                    $picture,
                    [
                        "folder" => "kaherecode/tutorials/",
                        "format" => "webp"
                    ]
                );
                $thumbnailURL = "https://res.cloudinary.com/{$_ENV['CLOUDINARY_CLOUD']}/{$upload['resource_type']}/{$upload['type']}/c_thumb,w_400,g_face/v{$upload['version']}/{$upload['public_id']}.{$upload['format']}";

                $tutorial->setPictureURL($upload['secure_url']);
                $tutorial->setThumbnailURL($thumbnailURL);
            }

            $tutorial->setAuthor($this->getUser());
            $em->persist($tutorial);
            $em->flush();

            return $this->redirectToRoute(
                'edit_tutorial',
                ['uuid' => $tutorial->getUuid()]
            );
        }

        return $this->render(
            'tutorial/tutorial_form.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/tutorials/{uuid}/edit", name="edit_tutorial")
     */
    public function edit(Request $request, Tutorial $tutorial)
    {
        /**
         * @var FormInterface
         */
        $form = $this->createForm(TutorialType::class, $tutorial);

        return $this->render(
            'tutorial/tutorial_form.html.twig',
            ['form' => $form->createView(), 'tutorial' => $tutorial]
        );
    }
}
