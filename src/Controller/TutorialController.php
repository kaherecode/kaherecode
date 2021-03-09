<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Comment;
use App\Service\Mailer;
use App\Entity\Tutorial;
use App\Form\CommentType;
use App\Form\TutorialType;
use App\Service\CloudinaryService;
use App\Repository\CommentRepository;
use App\Repository\TutorialRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TutorialController extends AbstractController
{
    private const WORD_PER_MIN = 250;

    /**
     * @Route("/", name="homepage")
     */
    public function index(TutorialRepository $tutorialRepository)
    {
        $tutorials = $tutorialRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC'],
            10,
            0
        );

        $jsTutorials = $tutorialRepository->findAllPublishedByTag('javascript');

        return $this->render(
            'tutorials/index.html.twig',
            ['tutorials' => $tutorials, 'jsTutorials' => $jsTutorials]
        );
    }

    /**
     * @Route("/tutorials", name="tutorials")
     */
    public function tutorials(
        Request $request,
        TutorialRepository $tutorialRepository
    ) {
        $tutorials = $tutorialRepository->findBy(
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
    public function tutorialsByTag(
        Tag $tag,
        TutorialRepository $tutorialRepository
    ) {
        $tutorials = $tutorialRepository->findAllPublishedByTag($tag->getLabel());

        return $this->render(
            'tutorials/tutorials.html.twig',
            [
                'tag' => $tag,
                'tutorials' => $tutorials
            ]
        );
    }

    /**
     * @Route("/tutorial/{slug}", name="tutorial_view")
     */
    public function show(
        Tutorial $tutorial,
        TutorialRepository $tutorialRepository,
        CommentRepository $commentRepository
    ) {
        $relatedTutorials = [];
        $userLastPublishedTutorial = $tutorialRepository
            ->getUserLastPublishedTutorial($tutorial);
        if ($userLastPublishedTutorial) {
            $relatedTutorials[] = $userLastPublishedTutorial;
        }
        $relatedTutorials = array_unique(
            array_merge(
                $relatedTutorials,
                $tutorialRepository->findRelatedTutorials($tutorial)
            ),
            SORT_REGULAR
        );

        $comments = $commentRepository->getTutorialComments($tutorial);

        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);

        return $this->render(
            'tutorials/show.html.twig',
            [
                'tutorial' => $tutorial,
                'relatedTutorials' => $relatedTutorials,
                'comments' => $comments,
                'form' => $commentForm->createView()
            ]
        );
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

            $tutorial->setTags(
                $this->buildTags($form->get('tags')->getData())
            );

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

            $tutorial->setTags(
                $this->buildTags($form->get('tags')->getData())
            );

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
            // calculate read time
            $wordCount = str_word_count(strip_tags($tutorial->getContent()));
            $minutes = (int) ceil($wordCount / self::WORD_PER_MIN);
            $tutorial->setReadTime($minutes);

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
    public function deleteTutorial(
        Tutorial $tutorial,
        Security $security,
        CloudinaryService $uploader
    ) {
        $this->denyAccessUnlessGranted('edit', $tutorial);

        if (!$tutorial->getIsPublished() || $security->isGranted('ROLE_ADMIN')) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tutorial);
            $em->flush();

            if ($tutorial->getPictureURL() !== null) {
                $uploader->delete($tutorial->getPictureURL());
            }

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

    protected function buildTags(string $tagsString): array
    {
        $em = $this->getDoctrine()->getManager();
        $tagsObject = [];

        $tags = array_filter(
            explode(",", $tagsString),
            fn ($c) => ctype_alnum(trim($c))
        );

        foreach ($tags as $tag) {
            if (trim($tag)) {
                // check if the tag already exists
                $existingTag = $em
                        ->getRepository(Tag::class)
                        ->findOneByLabel(strtolower(trim($tag)));

                if ($existingTag) { // if it is, simply add it to the tags array
                    $tagsObject[] = $existingTag;
                } else { // if not create a new one the add it to the tags array
                    $t = new Tag();
                    $t->setLabel(trim($tag));
                    $tagsObject[] = $t;
                }
            }
        }

        return $tagsObject;
    }
}
