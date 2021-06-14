<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const DEFAULT_USER_REFERENCE = 'default-user';

    /** @var UserPasswordHasherInterface */
    private $_passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->_passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setFullName('Mamadou Aliou Diallo');
        $user->setEmail('kaherecode@mail.com');
        $user->setUsername('kaherecode');
        $user->setPassword(
            $this->_passwordEncoder->hashPassword(
                $user,
                '123$ecreT'
            )
        );
        $user->setEnabled(true);

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::DEFAULT_USER_REFERENCE, $user);
    }
}
