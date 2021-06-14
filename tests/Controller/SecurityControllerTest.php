<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $translator->trans('Log In'));

        $client->submitForm(
            'login',
            ['email' => 'kaherecode@mail.com', 'password' => '123$ecreT']
        );

        $this->assertResponseRedirects('/');
    }

    public function testLoginWithWrongCredentials()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

        $client->catchExceptions(false);
        $client->followRedirects();
        $crawler = $client->request('GET', '/login');

        $client->submitForm(
            'login',
            ['email' => 'kaherecode@mail.co', 'password' => '123$ecre']
        );

        $this->assertStringContainsString(
            $translator->trans('Email could not be found'),
            $client->getResponse()->getContent()
        );
    }

    public function testLoginWithNotConfirmedAccount()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

        $client->catchExceptions(false);
        $client->followRedirects();

        $crawler = $client->request('GET', '/register');
        $client->submitForm(
            'signup',
            [
                'user_registration[fullName]' => 'Orion',
                'user_registration[email]' => 'orion@mail.com',
                'user_registration[username]' => 'orion',
                'user_registration[password]' => 'Ori@n_20',
            ]
        );

        $crawler = $client->request('GET', '/login');
        $client->submitForm(
            'login',
            ['email' => 'orion@mail.com', 'password' => 'Ori@n_20']
        );

        $this->assertStringContainsString(
            $translator->trans('Please activate your account before you log in. Check your emails for confirmation.'),
            $client->getResponse()->getContent()
        );
    }

    public function testLoginWithArchivedAccount()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

        $client->catchExceptions(false);
        $client->followRedirects();

        $crawler = $client->request('GET', '/register');
        $client->submitForm(
            'signup',
            [
                'user_registration[fullName]' => 'Orion',
                'user_registration[email]' => 'orion@mail.com',
                'user_registration[username]' => 'orion',
                'user_registration[password]' => 'Ori@n_20',
            ]
        );

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('orion@mail.com');
        $user->setEnabled(true);
        $user->setArchived(true);

        $em = static::getContainer()->get('doctrine.orm.default_entity_manager');
        $em->flush();

        $crawler = $client->request('GET', '/login');
        $client->submitForm(
            'login',
            ['email' => 'orion@mail.com', 'password' => 'Ori@n_20']
        );

        $this->assertStringContainsString(
            $translator->trans('Your account have been deactivated.'),
            $client->getResponse()->getContent()
        );
    }
}
