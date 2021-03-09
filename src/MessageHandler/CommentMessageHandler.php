<?php

namespace App\MessageHandler;

use App\Entity\Comment;
use App\Service\Mailer;
use App\Service\SpamChecker;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    /**
     * @var SpamChecker
     */
    protected $spamChecker;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CommentRepository
     */
    protected $commentRepository;

    /**
     * @var Mailer
     */
    protected $mailer;

    public function __construct(
        SpamChecker $spamChecker,
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository,
        Mailer $mailer
    ) {
        $this->spamChecker = $spamChecker;
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->mailer = $mailer;
    }

    public function __invoke(CommentMessage $message)
    {
        /**
         * @var Comment
         */
        $comment = $this->commentRepository->find($message->getId());
        if (! $comment) {
            return;
        }

        if ($this->spamChecker->isSpam($comment, $message->getContext())) {
            $comment->setState(Comment::STATE_SPAM);
        } else {
            $comment->setState(Comment::STATE_PUBLISHED);

            $this->mailer->sendNewCommentMessageToAuthor($comment);

            if ($comment->getReplyTo()) {
                $this->mailer->sendNewCommentMessageToDiscussion($comment);
            }
        }

        $this->entityManager->flush();
    }
}
