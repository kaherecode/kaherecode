<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Service\Mailer;
use App\Entity\Tutorial;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/{uuid}", name="add_comment", methods={"POST"})
     */
    public function addComment(
        Tutorial $tutorial,
        Request $request,
        CommentRepository $commentRepository,
        Mailer $mailer
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

                // TODO: if a reply, notify parent comment author
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        // TODO: check for spam
        // TODO: send a mail to author if comment not spam (published state)
        $mailer->sendNewCommentMessageToSupport($comment);

        $target = $this->generateUrl(
            'tutorial_view',
            ['slug' => $tutorial->getSlug()]
        );

        return $this->redirect("{$target}#comments");
    }

    /**
     * @Route("/comment/{id}/delete", name="delete_comment")
     */
    public function delete(Comment $comment)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($this->getUser() === $comment->getAuthor() or $this->isGranted('ROLE_ADMIN')) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();

            $target = $this->generateUrl(
                'tutorial_view',
                ['slug' => $comment->getTutorial()->getSlug()]
            );

            return $this->redirect("{$target}#comments");
        }

        throw new AccessDeniedException();
    }
}
