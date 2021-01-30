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

    public function testUserAccountConfirmation()
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

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('orion@mail.com');
        $client->request(
            'GET',
            '/register/confirmation/' .$user->getConfirmationToken()
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            'Bienvenue orion! Ton compte à été valider!'
        );
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
        $this->assertStringContainsString(
            'This value is already used.',
            $client->getResponse()->getContent()
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
        $this->assertStringContainsString(
            'This value is already used.',
            $client->getResponse()->getContent()
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

    public function testPasswordResetRequestWithNonExistingEmail()
    {
        $client = static::createClient();
        $client->request('GET', '/password-reset/request');
        $this->assertResponseIsSuccessful();

        $client->submitForm(
            'requestPasswordReset',
            ['email' => 'whatever@mail.com']
        );
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            'This email address is not registered.',
            $client->getResponse()->getContent()
        );
    }

    public function testPasswordResetWithDifferentPasswordConfirmation()
    {
        $client = static::createClient();
        $client->request('GET', '/password-reset/request');

        $client->submitForm(
            'requestPasswordReset',
            ['email' => 'kaherecode@mail.com']
        );

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->request(
            'GET',
            '/reset-password/' .$user->getConfirmationToken()
        );

        $client->submitForm(
            'resetPassword',
            ['password' => '1234$ecreT', 'confirmPassword' => '1234$eceT']
        );
        $this->assertStringContainsString(
            'Passwords are not the same.',
            $client->getResponse()->getContent()
        );
    }

    public function testPasswordResetWithNoSecurePassword()
    {
        $client = static::createClient();
        $client->request('GET', '/password-reset/request');

        $client->submitForm(
            'requestPasswordReset',
            ['email' => 'kaherecode@mail.com']
        );

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->request(
            'GET',
            '/reset-password/' .$user->getConfirmationToken()
        );

        $client->submitForm(
            'resetPassword',
            ['password' => '123', 'confirmPassword' => '123']
        );
        $this->assertStringContainsString(
            'Password is not valid. Sould be 8 or more characters. Should contains at least 1 special chars, 1 digit and 1 uppercace letter.',
            $client->getResponse()->getContent()
        );
    }

    public function testPasswordReset()
    {
        $client = static::createClient();
        $client->request('GET', '/password-reset/request');
        $this->assertResponseIsSuccessful();

        $client->submitForm(
            'requestPasswordReset',
            ['email' => 'kaherecode@mail.com']
        );
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            'A mail have been sent to you, check it to update your password.',
            $client->getResponse()->getContent()
        );

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->request(
            'GET',
            '/reset-password/' .$user->getConfirmationToken()
        );
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifie ton mot de passe');

        $client->submitForm(
            'resetPassword',
            ['password' => '1234$ecreT', 'confirmPassword' => '1234$ecreT']
        );
        $this->assertResponseRedirects('/login');

        $passwordEncoder = static::$container->get(
            'security.user_password_encoder.generic'
        );
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $this->assertTrue($passwordEncoder->isPasswordValid($user, '1234$ecreT'));
    }
}
