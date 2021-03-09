<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Tutorial;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TutorialFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $phpTag = new Tag();
        $phpTag->setLabel('php');

        $symfonyTag = new Tag();
        $symfonyTag->setLabel('symfony');

        $jsTag = new Tag();
        $jsTag->setLabel('javascript');

        $tutorial = new Tutorial();
        $tutorial->setTitle('Symfony and API Platform tutorial');
        $tutorial->setCreatedAt(new \DateTime);
        $tutorial->setContent('Looooonnnnnnnnggggg content');
        $tutorial->setDescription('description');
        $tutorial->setIsPublished(true);
        $tutorial->setPublishedAt(new \DateTime);
        $tutorial->addTag($phpTag);
        $tutorial->addTag($symfonyTag);
        $tutorial->setAuthor(
            $this->getReference(UserFixtures::DEFAULT_USER_REFERENCE)
        );
        $manager->persist($tutorial);

        $tutorial2 = new Tutorial();
        $tutorial2->setTitle('Create a blog with Symfony');
        $tutorial2->setCreatedAt(new \DateTime);
        $tutorial2->setContent('Looooonnnnnnnnggggg content');
        $tutorial2->setDescription('description');
        $tutorial2->setIsPublished(true);
        $tutorial2->setPublishedAt(new \DateTime);
        $tutorial2->addTag($phpTag);
        $tutorial2->addTag($symfonyTag);
        $tutorial2->setAuthor(
            $this->getReference(UserFixtures::DEFAULT_USER_REFERENCE)
        );
        $manager->persist($tutorial2);

        $tutorial3 = new Tutorial();
        $tutorial3->setTitle('Introdution to Javascript');
        $tutorial3->setCreatedAt(new \DateTime);
        $tutorial3->setContent('Looooonnnnnnnnggggg content');
        $tutorial3->setDescription('description');
        $tutorial3->setIsPublished(true);
        $tutorial3->setPublishedAt(new \DateTime);
        $tutorial3->addTag($jsTag);
        $tutorial3->setAuthor(
            $this->getReference(UserFixtures::DEFAULT_USER_REFERENCE)
        );
        $manager->persist($tutorial3);

        $tutorial4 = new Tutorial();
        $tutorial4->setTitle('Javascript non published');
        $tutorial4->setCreatedAt(new \DateTime);
        $tutorial4->setContent('Looooonnnnnnnnggggg content');
        $tutorial4->setDescription('description');
        $tutorial4->setIsPublished(false);
        $tutorial4->setPublishedAt(new \DateTime);
        $tutorial4->addTag($jsTag);
        $tutorial4->setAuthor(
            $this->getReference(UserFixtures::DEFAULT_USER_REFERENCE)
        );
        $manager->persist($tutorial4);

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @psalm-return array<class-string<FixtureInterface>>
     */
    public function getDependencies()
    {
        return [UserFixtures::class];
    }
}
