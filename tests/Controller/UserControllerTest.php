<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
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
                'user_registration[email]' => 'orion@mail.com',
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

    public function testProfileWithNoLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/profile');

        $this->assertResponseRedirects('/login');
    }

    public function testProfile()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->loginUser($user);

        $client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mamadou Aliou Diallo');
    }

    public function testEdittProfileWithNoLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/profile/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testEditProfileWithWrongPassword()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifie ton profil');

        $client->submitForm(
            'editProfile',
            [
                'profile[fullName]' => 'Mamadou Aliou Diallo',
                'profile[email]' => 'kaherecode@mail.com',
                'profile[username]' => 'kaherecode',
                'profile[currentPassword]' => 'Ori@n_20',
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            'Your password is not correct, try again!',
            $client->getResponse()->getContent()
        );
    }

    public function testEditProfile()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifie ton profil');

        $client->submitForm(
            'editProfile',
            [
                'profile[fullName]' => 'Mamadou Aliou Diallo',
                'profile[email]' => 'kaherecode@mail.com',
                'profile[username]' => 'kaherecode',
                'profile[github]' => 'kaherecode',
                'profile[linkedin]' => 'kaherecode',
                'profile[twitter]' => 'kaherecode',
                'profile[website]' => 'https://www.kaherecode.com',
                'profile[bio]' => 'An aspiring community for web developers',
                'profile[currentPassword]' => '123$ecreT',
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            'Your profile have been successfully updated!',
            $client->getResponse()->getContent()
        );
    }
}
