<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Service\Mailer;
use App\Entity\Tutorial;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
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
        Mailer $mailer,
        MessageBusInterface $bus
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

        // check for spam
        $context = [
            'user_ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('user-agent'),
            'referrer' => $request->headers->get('referer'),
            'permalink' => $request->getUri(),
        ];
        $bus->dispatch(new CommentMessage($comment->getId(), $context));

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
