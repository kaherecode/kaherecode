<?php

namespace App\Command;

use App\Service\SitemapGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSitemapXMLCommand extends Command
{
    protected static $defaultName = 'app:generate-sitemap-xml';
    protected static $defaultDescription = 'Generate the entire sitemap.xml file or adds an URL to it';

    /**
     * @var SitemapGenerator
     */
    protected $sitemapGenerator;

    public function __construct(SitemapGenerator $sitemapGenerator)
    {
        $this->sitemapGenerator = $sitemapGenerator;

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

            $res = $this->sitemapGenerator->addUrl($url);

            if (!$res) {
                $io->error("There is no sitemap.xml file, first generate one with app:generate-sitemap-xml");
                return Command::FAILURE;
            }
        } else {
            $this->sitemapGenerator->generateSitemap();
        }

        $io->success('sitemap.xml file have been successfully (re)generated.');

        return Command::SUCCESS;
    }
}
