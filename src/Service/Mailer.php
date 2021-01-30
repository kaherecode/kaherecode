<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

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
            // TODO: create a event subscriber to set the same from address for the whole app
            ->from(
                new Address(
                    'contact@kaherecode.com',
                    'Aliou de Kaherecode'
                )
            )
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject("Bienvenue sur Kaherecode {$user->getFullName()}!")
            ->htmlTemplate('emails/signup.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    public function sendRequestPasswordMessage(User $user)
    {
        $email = (new TemplatedEmail())
            // TODO: create a event subscriber to set the same from address for the whole app
            ->from(
                new Address(
                    'contact@kaherecode.com',
                    'Aliou de Kaherecode'
                )
            )
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject("Modifie ton mot de passe sur Kaherecode")
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }
}
