<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Tutorial;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        MailerInterface $mailer,
        TranslatorInterface $translator
    ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    public function sendSignUpMessage(User $user)
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject(
                htmlspecialchars(
                    $this->translator->trans(
                        "email.signup_title",
                        ['fullName' => $user->getFullName()]
                    ),
                    \ENT_COMPAT | \ENT_HTML5
                )
            )
            ->htmlTemplate('emails/signup.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    public function sendRequestPasswordMessage(User $user)
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject($this->translator->trans("email.update_password_title"))
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    public function sendTutorialPublishedMessage(Tutorial $tutorial)
    {
        $email = (new NotificationEmail())
            ->to($_ENV['FROM_EMAIL'])
            ->subject($this->translator->trans("email.new_tutorial_title"))
            ->htmlTemplate('emails/new_tutorial_notification.html.twig')
            ->context(['tutorial' => $tutorial]);

        $this->mailer->send($email);
    }

    public function sendNewCommentMessageToSupport(Comment $comment)
    {
        $email = (new NotificationEmail())
            ->to($_ENV['FROM_EMAIL'])
            ->subject($this->translator->trans('email.new_comment_support_title'))
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
            ->subject($this->translator->trans("email.new_comment_author_title"))
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
            ->subject($this->translator->trans("email.new_comment_response_title"))
            ->htmlTemplate('emails/new_comment_support_notification.html.twig')
            ->context(['comment' => $comment]);

        $this->mailer->send($email);
    }

    public function sendUserArchivedMessage(User $user)
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject($this->translator->trans("email.user_archived_title"))
            ->htmlTemplate('emails/user_archived.html.twig')
            ->context(['user' => $user, 'mail' => $_ENV['FROM_EMAIL']]);

        $this->mailer->send($email);
    }

    public function sendUserArchivedMessageToSupport(User $user)
    {
        $email = (new NotificationEmail())
            ->to($_ENV['FROM_EMAIL'])
            ->subject($this->translator->trans('email.user_archived_support_title'))
            ->htmlTemplate('emails/user_archived_support_notification.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }
}
