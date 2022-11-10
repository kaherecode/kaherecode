<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserControllerTest extends WebTestCase
{
    private $client;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EntityManager */
    private $entityManager;

    /** @var UserPasswordHasherInterface */
    private $passwordEncoder;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->translator = self::getContainer()->get('translator');
        $this->passwordEncoder = self::getContainer()
            ->get('security.password_hasher');
        $this->entityManager = self::getContainer()
            ->get('doctrine')->getManager();
    }

    public function testRegister(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm(
            'signup',
            [
                'user_registration[fullName]' => 'Orion',
                'user_registration[email]' => 'orion1@mail.com',
                'user_registration[username]' => 'orion1',
                'user_registration[password]' => 'Ori@n_20',
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            $this->translator
                ->trans('notifications.check_email_for_confirmation'),
            $this->client->getResponse()->getContent()
        );
    }

    public function testUserAccountConfirmation(): void
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
        $this->assertResponseIsSuccessful();

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'orion@mail.com']);
        $this->client->request(
            'GET',
            '/register/confirmation/' . $user->getConfirmationToken()
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans(
                'user.account_validated',
                ['username' => 'orion']
            )
        );
    }

    public function testRegisterDuplicateEmail(): void
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

        // submit another form with the same informations
        $this->client->submitForm(
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
            $this->translator->trans('This value is already used'),
            $this->client->getResponse()->getContent()
        );
    }

    public function testRegisterDuplicateUsername(): void
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

        // submit another form with the same informations
        $this->client->submitForm(
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
            $this->translator->trans('This value is already used.'),
            $this->client->getResponse()->getContent()
        );
    }

    public function testProfileWithNoLogin(): void
    {
        $this->client->request('GET', '/profile');

        $this->assertResponseRedirects('/login');
    }

    public function testProfile(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'kaherecode@mail.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mamadou Aliou Diallo');
    }

    public function testEditProfileWithNoLogin(): void
    {
        $this->client->request('GET', '/profile/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testEditProfileWithWrongPassword(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'kaherecode@mail.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('title.update_my_profile')
        );

        $this->client->submitForm(
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
            $this->translator->trans('notifications.wrong_password'),
            $this->client->getResponse()->getContent()
        );
    }

    public function testEditProfile(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'kaherecode@mail.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('title.update_my_profile')
        );

        $this->client->submitForm(
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
            $this->translator->trans('notifications.profile_updated'),
            $this->client->getResponse()->getContent()
        );
    }

    public function testPasswordResetRequestWithNonExistingEmail(): void
    {
        $this->client->request('GET', '/password-reset/request');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm(
            'requestPasswordReset',
            ['email' => 'whatever@mail.com']
        );
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            $this->translator->trans('notifications.not_registered_email'),
            $this->client->getResponse()->getContent()
        );
    }

    public function testPasswordResetWithDifferentPasswordConfirmation(): void
    {
        $this->client->request('GET', '/password-reset/request');

        $this->client->submitForm(
            'requestPasswordReset',
            ['email' => 'kaherecode@mail.com']
        );

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'kaherecode@mail.com']);
        $this->client->request(
            'GET',
            '/reset-password/' . $user->getConfirmationToken()
        );

        $this->client->submitForm(
            'resetPassword',
            ['password' => '1234$ecreT', 'confirmPassword' => '1234$eceT']
        );
        $this->assertStringContainsString(
            $this->translator->trans('notifications.different_password'),
            $this->client->getResponse()->getContent()
        );
    }

    public function testPasswordResetWithNoSecurePassword(): void
    {
        $this->client->request('GET', '/password-reset/request');

        $this->client->submitForm(
            'requestPasswordReset',
            ['email' => 'kaherecode@mail.com']
        );

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'kaherecode@mail.com']);
        $this->client->request(
            'GET',
            '/reset-password/' . $user->getConfirmationToken()
        );

        $this->client->submitForm(
            'resetPassword',
            ['password' => '123', 'confirmPassword' => '123']
        );
        $this->assertStringContainsString(
            $this->translator->trans('notifications.not_valid_password'),
            $this->client->getResponse()->getContent()
        );
    }

    public function testPasswordReset(): void
    {
        $this->client->request('GET', '/password-reset/request');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm(
            'requestPasswordReset',
            ['email' => 'kaherecode@mail.com']
        );
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            $this->translator->trans('notifications.check_email_for_password'),
            $this->client->getResponse()->getContent()
        );

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $this->client->request(
            'GET',
            '/reset-password/' . $user->getConfirmationToken()
        );
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('title.change_password')
        );

        $this->client->submitForm(
            'resetPassword',
            ['password' => '1234$ecreT', 'confirmPassword' => '1234$ecreT']
        );
        $this->assertResponseRedirects('/login');

        $user = $userRepository->findOneByEmail('kaherecode@mail.com');
        $this->assertTrue(
            $this->passwordEncoder->isPasswordValid($user, '1234$ecreT')
        );
    }
}
