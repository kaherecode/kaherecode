<?php

namespace App\Service;

use App\Entity\User;
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
}
