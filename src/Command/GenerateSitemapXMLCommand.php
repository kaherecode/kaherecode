<?php

namespace App\Command;

use App\Model\SitemapUrl;
use App\Repository\TagRepository;
use App\Repository\TutorialRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class GenerateSitemapXMLCommand extends Command
{
    const FILENAME = 'sitemap.xml';

    protected static $defaultName = 'app:generate-sitemap-xml';
    protected static $defaultDescription = 'Generate the entire sitemap.xml
        file or adds an URL to it';

    protected $projectRootDir;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Serializer
     */
    protected $serializer;

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

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'url',
                InputArgument::OPTIONAL,
                'The url to adds to the sitemap.xml file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');

        if ($url) {
            $io->note(sprintf("Adding the URL <{$url}> to the sitemap.xml file"));
        } else {
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

            // open the base sitemap file
            // if exists adds the value of $xmlString before </urlset> closing tag
            // else wrap the value of $xmlString in a <urlset></urlset> tag

            file_put_contents(
                "{$this->projectRootDir}/" . self::FILENAME,
                $xmlString
            );
        }

        $io->success('sitemap.xml file have been successfully (re)generated.');

        return Command::SUCCESS;
    }
}
