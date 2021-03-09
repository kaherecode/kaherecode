<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TutorialControllerTest extends WebTestCase
{
    private static $translator;
    private static $client;

    public function setUp()
    {
        self::$client = static::createClient();
        $kernel = self::bootKernel();

        self::$translator = self::$container->get('translator');
    }

    public function testIndex()
    {
        $crawler = self::$client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Learn. Code. Share.');
        $this->assertStringContainsString(
            'Symfony and API Platform tutorial',
            self::$client->getResponse()->getContent()
        );
        $this->assertStringNotContainsString(
            'Javascript non published',
            self::$client->getResponse()->getContent()
        );
    }

    public function testTutorials()
    {
        $crawler = self::$client->request('GET', '/tutorials');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            self::$translator->trans('Tutorial lists')
        );
        $this->assertStringContainsString(
            'Symfony and API Platform tutorial',
            self::$client->getResponse()->getContent()
        );
        $this->assertStringNotContainsString(
            'Javascript non published',
            self::$client->getResponse()->getContent()
        );
    }
}
