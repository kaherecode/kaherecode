<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testRegister()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');
        $client->submitForm(
            'signup',
            [
                'user_registration[fullName]' => 'Orion',
                'user_registration[email]' => 'aliou.diallo@kaherecode.com',
                'user_registration[username]' => 'orion',
                'user_registration[password]' => 'Ori@n_20',
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('small', 'Thanks for registering.');
    }

    public function testRegisterDuplicateEmail()
    {
        $client = static::createClient();
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

        // submit another form with the same informations
        $client->submitForm(
            'signup',
            [
                'user_registration[fullName]' => 'Orion',
                'user_registration[email]' => 'orion@mail.com',
                'user_registration[username]' => 'orion',
                'user_registration[password]' => 'Ori@n_20',
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'small',
            'This email address is already taken.'
        );
    }

    public function testRegisterDuplicateUsername()
    {
        $client = static::createClient();
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

        // submit another form with the same informations
        $client->submitForm(
            'signup',
            [
                'user_registration[fullName]' => 'Orion',
                'user_registration[email]' => 'orion_@mail.com',
                'user_registration[username]' => 'orion',
                'user_registration[password]' => 'Ori@n_20',
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'small',
            'This username is already taken.'
        );
    }
}
