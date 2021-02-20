<?php

namespace App\MessageHandler;

use App\Entity\Comment;
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

    public function __construct(
        SpamChecker $spamChecker,
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository
    ) {
        $this->spamChecker = $spamChecker;
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
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

            // TODO: if a reply, notify parent comment author
            // TODO: send a mail to author if comment not spam (published state)
        }

        $this->entityManager->flush();
    }
}
