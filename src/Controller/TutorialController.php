<?php

namespace App\Controller;

use App\Service\Mailer;
use App\Entity\Tag;
use App\Entity\Tutorial;
use App\Form\TutorialType;
use App\Service\CloudinaryService;
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
        $em = $this->getDoctrine()->getManager();
        $tutorials = $em->getRepository(Tutorial::class)->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC'],
            10,
            0
        );

        return $this->render(
            'tutorials/index.html.twig',
            ['tutorials' => $tutorials]
        );
    }

    /**
     * @Route("/tutorials", name="tutorials")
     */
    public function tutorials(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tutorials = $em->getRepository(Tutorial::class)->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC']
        );

        return $this->render(
            'tutorials/tutorials.html.twig',
            ['tutorials' => $tutorials]
        );
    }

    /**
     * @Route("/tag/{label}", name="tag_tutorials")
     */
    public function tutorialsByTag(Tag $tag)
    {
        // TODO: define a function in repo to get tutorials
        $em = $this->getDoctrine()->getManager();
        $tutorials = $em->getRepository(Tutorial::class)->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC']
        );

        return $this->render(
            'tutorials/tutorials.html.twig',
            ['tag' => $tag, 'tutorials' => $tutorials]
        );
    }

    /**
     * @Route("/tutorial/{slug}", name="tutorial_view")
     */
    public function show(Tutorial $tutorial)
    {
        return $this->render('tutorials/show.html.twig', ['tutorial' => $tutorial]);
    }

    /**
     * @Route("/tutorials/new", name="new_tutorial")
     */
    public function create(
        Request $request,
        CloudinaryService $uploader
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

            // set the tutorial tags
            $tags = array_filter(
                explode(",", $form->get('tags')->getData()),
                fn ($c) => ctype_alnum(trim($c))
            );
            foreach ($tags as $cat) {
                if (trim($cat)) {
                    // check if the tag already exists
                    $existingTag = $em
                        ->getRepository(Tag::class)
                        ->findOneByLabel(strtolower(trim($cat)));

                    if ($existingTag) { // if it is, simply add it to tutorial
                        $tutorial->addTag($existingTag);
                    } else { // if not create a new one the add it to tutorial
                        $tag = new Tag();
                        $tag->setLabel(trim($cat));
                        $tutorial->addTag($tag);
                        $em->persist($tag);
                    }
                }
            }

            $picture = $form->get("picture")->getData();
            if ($picture) {
                $upload = $uploader->upload($picture);

                $tutorial->setPictureURL($upload['fileURL']);
                $tutorial
                    ->setThumbnailURL($upload['thumbnailURL'] ?? $upload['fileURL']);
            }

            $tutorial->setAuthor($this->getUser());
            $tutorial->setContent($request->get("htmlContent"));
            $em->persist($tutorial);
            $em->flush();

            return $this->redirectToRoute(
                'edit_tutorial',
                ['uuid' => $tutorial->getUuid()]
            );
        }

        return $this->render(
            'tutorials/tutorial_form.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/tutorials/{uuid}/edit", name="edit_tutorial")
     */
    public function edit(
        Request $request,
        Tutorial $tutorial,
        CloudinaryService $uploader
    ) {
        $this->denyAccessUnlessGranted('edit', $tutorial);

        /**
         * @var FormInterface
         */
        $form = $this->createForm(TutorialType::class, $tutorial);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // remove all tags from tutorial
            foreach ($tutorial->getTags() as $tag) {
                $tutorial->removeTag($tag);
            }

            // set the tutorial tags
            $tags = array_filter(
                explode(",", $form->get('tags')->getData()),
                fn ($c) => ctype_alnum(trim($c))
            );
            foreach ($tags as $cat) {
                if (trim($cat)) {
                    // check if the tag already exists
                    $existingTag = $em
                        ->getRepository(Tag::class)
                        ->findOneByLabel(strtolower(trim($cat)));

                    if ($existingTag) { // if it is, simply add it to tutorial
                        $tutorial->addTag($existingTag);
                    } else { // if not create a new one the add it to tutorial
                        $tag = new Tag();
                        $tag->setLabel(trim($cat));
                        $tutorial->addTag($tag);
                        $em->persist($tag);
                    }
                }
            }

            $picture = $form->get("picture")->getData();
            if ($picture) {
                if ($tutorial->getPictureURL() !== null) {
                    $uploader->delete($tutorial->getPictureURL());
                }

                $upload = $uploader->upload($picture);

                $tutorial->setPictureURL($upload['fileURL']);
                $tutorial
                    ->setThumbnailURL($upload['thumbnailURL'] ?? $upload['fileURL']);
            }

            if (! empty($request->get("htmlContent"))) {
                $tutorial->setContent($request->get("htmlContent"));
            }
            $tutorial->setUpdatedAt(new \DateTime());

            $em->flush();
        }

        return $this->render(
            'tutorials/tutorial_form.html.twig',
            ['form' => $form->createView(), 'tutorial' => $tutorial]
        );
    }

    /**
     * @Route("/tutorials/{uuid}/preview", name="preview_tutorial")
     */
    public function tutorialPreview(Tutorial $tutorial)
    {
        return $this->render(
            'tutorials/preview.html.twig',
            ['tutorial' => $tutorial]
        );
    }

    /**
     * @Route("/tutorials/{uuid}/publish", name="publish_tutorial")
     */
    public function publishTutorial(Tutorial $tutorial, Mailer $mailer)
    {
        $this->denyAccessUnlessGranted('edit', $tutorial);

        if ($tutorial->getTitle() !== null && $tutorial->getTitle() !== ''
            && $tutorial->getContent() !== null && $tutorial->getContent() !== ''
            && $tutorial->getPictureURL() !== null
            && $tutorial->getPictureURL() !== ''
            && $tutorial->getDescription() !== null
            && $tutorial->getDescription() !== ''
            && count($tutorial->getTags()->toArray()) > 0
        ) {
            $tutorial->setIsPublished(true);
            $tutorial->setPublishedAt(new \DateTime);
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $mailer->sendTutorialPublishedMessage($tutorial);

            return $this->redirectToRoute(
                'tutorial_view',
                ['slug' => $tutorial->getSlug()]
            );
        }

        $this->addFlash(
            'error',
            'An error happened publishing the tutorial. Check that those fields
             have been filled: Picture, Title, Description, Tags, and Content.
             Then make sure to save the tutorial first before hitting the publish button.'
        );

        return $this->redirectToRoute(
            'edit_tutorial',
            ['uuid' => $tutorial->getUuid()]
        );
    }

    /**
     * @Route("/tutorials/{uuid}/delete", name="delete_tutorial")
     */
    public function deleteTutorial(Tutorial $tutorial)
    {
        $this->denyAccessUnlessGranted('edit', $tutorial);

        if (!$tutorial->getIsPublished()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tutorial);
            $em->flush();

            return $this->redirectToRoute('profile');
        } else {
            $this->addFlash(
                'error',
                "You can't delete a published tutorial, however you can archive it."
            );
        }

        return $this->redirectToRoute(
            'edit_tutorial',
            ['uuid' => $tutorial->getUuid()]
        );
    }
}
