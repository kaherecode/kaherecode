<?php

namespace App\EventListener;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailFromListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [MessageEvent::class => 'onMessage'];
    }

    public function onMessage(MessageEvent $event)
    {
        $email = $event->getMessage();

        if (!$email instanceof Email) {
            return;
        }

        $email->from(new Address($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']));
    }
}
