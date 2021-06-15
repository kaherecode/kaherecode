<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Tutorial;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class TutorialControllerTest extends WebTestCase
{
    private $client;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function setUp()
    {
        $this->client = static::createClient();

        $this->translator = self::getContainer()->get('translator');
        $this->entityManager = self::getContainer()
            ->get('doctrine')->getManager();
    }

    public function testIndex()
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Learn. Code. Share.');
        $this->assertStringContainsString(
            'Symfony and API Platform tutorial',
            $this->client->getResponse()->getContent()
        );
        $this->assertStringNotContainsString(
            'Javascript non published',
            $this->client->getResponse()->getContent()
        );
    }

    public function testTutorials()
    {
        $this->client->request('GET', '/tutorials');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('title.tutorials_list')
        );
        $this->assertStringContainsString(
            'Symfony and API Platform tutorial',
            $this->client->getResponse()->getContent()
        );
        $this->assertStringNotContainsString(
            'Javascript non published',
            $this->client->getResponse()->getContent()
        );
    }

    public function testTutorialsByTag()
    {
        $this->client->request('GET', '/tag/php');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            "{$this->translator->trans('title.tutorials_list')} - PHP"
        );
        $this->assertStringContainsString(
            'Symfony and API Platform tutorial',
            $this->client->getResponse()->getContent()
        );
        $this->assertStringNotContainsString(
            'Introduction to Javascript',
            $this->client->getResponse()->getContent()
        );
    }

    public function testShow()
    {
        $tutorial = $this->entityManager
            ->getRepository(Tutorial::class)
            ->findOneBy(['title' => 'Symfony and API Platform tutorial']);

        $this->client->request(
            'GET',
            '/tutorial/' . $tutorial->getSlug()
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('Symfony and API Platform tutorial')
        );
        $this->assertStringContainsString(
            'Looooonnnnnnnnggggg content',
            $this->client->getResponse()->getContent()
        );
        // asserts the related tutorials
        $this->assertStringContainsString(
            'Create a blog with Symfony',
            $this->client->getResponse()->getContent()
        );
    }

    public function testDeleteTutorialWithNoLogin()
    {
        $tutorial = $this->entityManager
            ->getRepository(Tutorial::class)
            ->findOneBy(['title' => 'Javascript non published']);

        $this->client->request(
            'GET',
            "/tutorials/{$tutorial->getUuid()}/delete"
        );

        $this->assertResponseRedirects('/login');
    }

    public function testDeletePublishedTutorialByRoleUser()
    {
        $tutorial = $this->entityManager
            ->getRepository(Tutorial::class)
            ->findOneBy(['title' => 'Symfony and API Platform tutorial']);
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'kaherecode@mail.com']);

        $this->client->loginUser($user);

        $this->client->request(
            'GET',
            "/tutorials/{$tutorial->getUuid()}/delete"
        );

        $this->assertResponseRedirects();
    }
}
