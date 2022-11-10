<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    /** @var EntityManager */
    private $entityManager;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()
            ->get('doctrine')->getManager();
    }

    public function testLogin(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm(
            'login',
            ['email' => 'kaherecode@mail.com', 'password' => '123$ecreT']
        );

        $this->assertResponseRedirects('/');
    }

    /** @runInSeparateProcess */
    public function testLoginWithWrongCredentials(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm(
            'login',
            ['email' => 'kaherecode@mail.co', 'password' => '123$ecre']
        );

        $this->assertResponseRedirects('/login');
    }

    /** @runInSeparateProcess */
    public function testLoginWithNotConfirmedAccount(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm(
            'signup',
            [
                'user_registration[fullName]' => 'Orion',
                'user_registration[email]' => 'orion@mail.com',
                'user_registration[username]' => 'orion',
                'user_registration[password]' => 'Ori@n_20',
            ]
        );

        $this->client->request('GET', '/login');
        $this->client->submitForm(
            'login',
            ['email' => 'orion@mail.com', 'password' => 'Ori@n_20']
        );

        $this->assertResponseRedirects('/login');
    }

    /** @runInSeparateProcess */
    public function testLoginWithArchivedAccount(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm(
            'signup',
            [
                'user_registration[fullName]' => 'Orion',
                'user_registration[email]' => 'orion@mail.com',
                'user_registration[username]' => 'orion',
                'user_registration[password]' => 'Ori@n_20',
            ]
        );

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'orion@mail.com']);
        $user->setEnabled(true);
        $user->setArchived(true);

        $this->entityManager->flush();

        $this->client->request('GET', '/login');
        $this->client->submitForm(
            'login',
            ['email' => 'orion@mail.com', 'password' => 'Ori@n_20']
        );

        $this->assertResponseRedirects('/login');
    }
}
