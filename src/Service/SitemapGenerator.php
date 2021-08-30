<?php

namespace App\Service;

use App\Model\SitemapUrl;
use App\Repository\TagRepository;
use App\Repository\TutorialRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SitemapGenerator
{
    const FILENAME = 'public/sitemap.xml';
    const BASE_SITEMAP_FILENAME = 'base_sitemap.xml';

    protected $projectRootDir;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * @var TutorialRepository
     */
    protected $tutorialRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(
        string $projectRootDir,
        RouterInterface $router,
        TagRepository $tagRepository,
        TutorialRepository $tutorialRepository,
        UserRepository $userRepository
    ) {
        $this->serializer = new Serializer(
            [new ObjectNormalizer()],
            [new XmlEncoder()]
        );
        $this->router = $router;
        $this->tagRepository = $tagRepository;
        $this->tutorialRepository = $tutorialRepository;
        $this->userRepository = $userRepository;
        $this->projectRootDir = $projectRootDir;
    }

    public function addUrl(string $url): int
    {
        if (file_exists(
            "{$this->projectRootDir}/" . self::FILENAME
        )) {
            $baseXml = file_get_contents(
                "{$this->projectRootDir}/" . self::FILENAME
            );
            $sUrl = new SitemapUrl();
            $sUrl->setLoc($url);
            $xmlUrl = $this->serializer->serialize(
                $sUrl,
                'xml',
                [
                    'xml_root_node_name' => 'url',
                    'xml_format_output' => true,
                    'encoder_ignored_node_types' => [\XML_PI_NODE]
                ]
            );

            $xmlString = substr_replace(
                $baseXml,
                $xmlUrl,
                strpos($baseXml, '</urlset>'),
                0
            );

            file_put_contents(
                "{$this->projectRootDir}/" . self::FILENAME,
                $xmlString
            );

            return 1;
        } else {
            return 0;
        }
    }

    public function generateSitemap()
    {
        $xmlString = '';
        $sitemapUrls = [];

        // adds tags links
        $tags = $this->tagRepository->findAll();
        foreach ($tags as $tag) {
            $sUrl = new SitemapUrl();
            $sUrl->setLoc(
                $this->router->generate(
                    'tag_tutorials',
                    ['label' => $tag->getLabel()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );

            $sitemapUrls[] = $sUrl;
        }

        // adds tutorials links
        $tutorials = $this->tutorialRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC']
        );
        foreach ($tutorials as $tutorial) {
            $sUrl = new SitemapUrl();
            $sUrl->setLoc(
                $this->router->generate(
                    'tutorial_view',
                    ['slug' => $tutorial->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );

            $sitemapUrls[] = $sUrl;
        }

        // adds users links
        $users = $this->userRepository->findBy(
            ['enabled' => true, 'archived' => false]
        );
        foreach ($users as $user) {
            $sUrl = new SitemapUrl();
            $sUrl->setLoc(
                $this->router->generate(
                    'show_user',
                    ['username' => $user->getUsername()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );

            $sitemapUrls[] = $sUrl;
        }

        foreach ($sitemapUrls as $value) {
            $xmlString .= $this->serializer->serialize(
                $value,
                'xml',
                [
                        'xml_root_node_name' => 'url',
                        'xml_format_output' => true,
                        'encoder_ignored_node_types' => [\XML_PI_NODE]
                    ]
            );
            $xmlString .= " \n";
        }

        if (file_exists(
            "{$this->projectRootDir}/" . self::BASE_SITEMAP_FILENAME
        )) {
            $baseXml = file_get_contents(
                "{$this->projectRootDir}/" . self::BASE_SITEMAP_FILENAME
            );
            // adds the content of $xmlString before </urlset> closing tag
            $xmlString = substr_replace(
                $baseXml,
                $xmlString,
                strpos($baseXml, '</urlset>'),
                0
            );
        } else {
            $xmlString = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"> \n" . $xmlString;
            $xmlString .= "</urlset>";
        }

        file_put_contents(
            "{$this->projectRootDir}/" . self::FILENAME,
            $xmlString
        );
    }
}
