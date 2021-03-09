<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Tutorial;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;

class Mailer
{
    /**
     * @var MailerInterface
     */
    protected $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendSignUpMessage(User $user)
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject("Bienvenue sur Kaherecode {$user->getFullName()}!")
            ->htmlTemplate('emails/signup.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    public function sendRequestPasswordMessage(User $user)
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject("Modifie ton mot de passe sur Kaherecode")
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    public function sendTutorialPublishedMessage(Tutorial $tutorial)
    {
        $email = (new NotificationEmail())
            ->to($_ENV['FROM_EMAIL'])
            ->subject("A new tutorial have been published!")
            ->htmlTemplate('emails/new_tutorial_notification.html.twig')
            ->context(['tutorial' => $tutorial]);

        $this->mailer->send($email);
    }

    public function sendNewCommentMessageToSupport(Comment $comment)
    {
        $email = (new NotificationEmail())
            ->to($_ENV['FROM_EMAIL'])
            ->subject("A new comment have just been submitted!")
            ->htmlTemplate('emails/new_comment_support_notification.html.twig')
            ->context(['comment' => $comment]);

        $this->mailer->send($email);
    }

    public function sendNewCommentMessageToAuthor(Comment $comment)
    {
        $email = (new TemplatedEmail())
            ->to(
                new Address(
                    $comment->getTutorial()->getAuthor()->getEmail(),
                    $comment->getTutorial()->getAuthor()->getFullName()
                )
            )
            ->subject("A new comment have just been submitted to your tutorial!")
            ->htmlTemplate('emails/new_comment_support_notification.html.twig')
            ->context(['comment' => $comment]);

        $this->mailer->send($email);
    }

    public function sendNewCommentMessageToDiscussion(Comment $comment)
    {
        $discussions = $comment->getReplyTo()->getResponses()->toArray();

        // get only published comments from discussions
        $discussions = array_filter(
            $discussions,
            fn ($c) => $c->getState() === Comment::STATE_PUBLISHED
        );

        // get published comments authors
        $authors = array_map(
            fn ($c) => $c->getAuthor(),
            $discussions
        );

        // get unique authors array
        $authors = array_unique($authors, SORT_REGULAR);

        // remove replyTo author from authors array
        $authors = array_filter(
            $authors,
            fn ($u) =>
                $u !== $comment->getReplyTo()->getAuthor()
                && $u !== $comment->getAuthor()
        );

        // build addresses
        $addresses = array_map(
            fn ($u) =>
                new Address(
                    $u->getEmail(),
                    $u->getFullName()
                ),
            $authors
        );

        $email = (new TemplatedEmail())
            ->to(
                new Address(
                    $comment->getReplyTo()->getAuthor()->getEmail(),
                    $comment->getReplyTo()->getAuthor()->getFullName()
                ),
            )
            ->addBcc(
                ...$addresses
            )
            ->subject("A response to your comment have just been submitted!")
            ->htmlTemplate('emails/new_comment_support_notification.html.twig')
            ->context(['comment' => $comment]);

        $this->mailer->send($email);
    }
}
