<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserControllerTest extends WebTestCase
{
    public function testRegister()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

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
        $this->assertSelectorTextContains(
            'small',
            $translator->trans('notifications.check_email_for_confirmation')
        );
    }

    public function testUserAccountConfirmation()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

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

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('orion@mail.com');
        $client->request(
            'GET',
            '/register/confirmation/' . $user->getConfirmationToken()
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $translator->trans(
                'user.account_validated',
                ['username' => 'orion']
            )
        );
    }

    public function testRegisterDuplicateEmail()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

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
            $translator->trans('This value is already used'),
            $client->getResponse()->getContent()
        );
    }

    public function testRegisterDuplicateUsername()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

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
            $translator->trans('This value is already used.'),
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
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->loginUser($user);

        $client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mamadou Aliou Diallo');
    }

    public function testEditProfileWithNoLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/profile/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testEditProfileWithWrongPassword()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $translator->trans('title.update_my_profile')
        );

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
            $translator->trans('notifications.wrong_password'),
            $client->getResponse()->getContent()
        );
    }

    public function testEditProfile()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $translator->trans('title.update_my_profile')
        );

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
            $translator->trans('notifications.profile_updated'),
            $client->getResponse()->getContent()
        );
    }

    public function testPasswordResetRequestWithNonExistingEmail()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

        $client->request('GET', '/password-reset/request');
        $this->assertResponseIsSuccessful();

        $client->submitForm(
            'requestPasswordReset',
            ['email' => 'whatever@mail.com']
        );
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            $translator->trans('notifications.not_registered_email'),
            $client->getResponse()->getContent()
        );
    }

    public function testPasswordResetWithDifferentPasswordConfirmation()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

        $client->request('GET', '/password-reset/request');

        $client->submitForm(
            'requestPasswordReset',
            ['email' => 'kaherecode@mail.com']
        );

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->request(
            'GET',
            '/reset-password/' . $user->getConfirmationToken()
        );

        $client->submitForm(
            'resetPassword',
            ['password' => '1234$ecreT', 'confirmPassword' => '1234$eceT']
        );
        $this->assertStringContainsString(
            $translator->trans('notifications.different_password'),
            $client->getResponse()->getContent()
        );
    }

    public function testPasswordResetWithNoSecurePassword()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

        $client->request('GET', '/password-reset/request');

        $client->submitForm(
            'requestPasswordReset',
            ['email' => 'kaherecode@mail.com']
        );

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->request(
            'GET',
            '/reset-password/' . $user->getConfirmationToken()
        );

        $client->submitForm(
            'resetPassword',
            ['password' => '123', 'confirmPassword' => '123']
        );
        $this->assertStringContainsString(
            $translator->trans('notifications.not_valid_password'),
            $client->getResponse()->getContent()
        );
    }

    public function testPasswordReset()
    {
        $client = static::createClient();
        $translator = static::getContainer()->get(TranslatorInterface::class);

        $client->request('GET', '/password-reset/request');
        $this->assertResponseIsSuccessful();

        $client->submitForm(
            'requestPasswordReset',
            ['email' => 'kaherecode@mail.com']
        );
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            $translator->trans('notifications.check_email_for_password'),
            $client->getResponse()->getContent()
        );

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $client->request(
            'GET',
            '/reset-password/' . $user->getConfirmationToken()
        );
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $translator->trans('title.change_password')
        );

        $client->submitForm(
            'resetPassword',
            ['password' => '1234$ecreT', 'confirmPassword' => '1234$ecreT']
        );
        $this->assertResponseRedirects('/login');

        $passwordEncoder = static::getContainer()->get(
            'security.user_password_encoder.generic'
        );
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $this->assertTrue(
            $passwordEncoder->isPasswordValid($user, '1234$ecreT')
        );
    }
}
