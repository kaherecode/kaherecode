<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Tutorial;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/{uuid}", name="add_comment", methods={"POST"})
     */
    public function addComment(
        Tutorial $tutorial,
        Request $request,
        CommentRepository $commentRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $comment->setTutorial($tutorial);
        $comment->setMarkdownContent(
            $request->request->get('comment')['markdownContent']
        );
        $comment->setContent($request->request->get('htmlContent'));

        if ($request->query->get('commentID')) {
            $replyTo = $commentRepository->find($request->query->get('commentID'));

            if ($replyTo) {
                $comment->setReplyTo($replyTo);
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        // TODO: check for spam
        // TODO: send a notif email to support and author
        // TODO: if a reply, notify parent comment author

        $target = $this->generateUrl(
            'tutorial_view',
            ['slug' => $tutorial->getSlug()]
        );

        return $this->redirect("{$target}#comments");
    }

    public function delete(Comment $comment)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
    }
}
