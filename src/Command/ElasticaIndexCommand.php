<?php

namespace App\Command;

use Elastica\Document;
use JoliCode\Elastically\Client;
use App\Repository\TutorialRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticaIndexCommand extends Command
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var TutorialRepository
     */
    protected $tutorialRepository;


    public function __construct(
        Client $client,
        TutorialRepository $tutorialRepository
    ) {
        $this->client = $client;
        $this->tutorialRepository = $tutorialRepository;

        parent::__construct();
    }

    protected static $defaultName = 'app:elastica:create-index';

    protected function configure()
    {
        $this->setDescription('Create Elasticsearch index and populate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $indexBuilder = $this->client->getIndexBuilder();
        $tutorialIndex = $indexBuilder->createIndex('tutorial');
        $indexer = $this->client->getIndexer();

        $tutorials = $this->tutorialRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC']
        );
        foreach ($tutorials as $tutorial) {
            $indexer->scheduleIndex(
                $tutorialIndex,
                new Document($tutorial->getId(), $tutorial->toModel())
            );
        }

        $indexer->flush();

        $indexBuilder->markAsLive($tutorialIndex, 'tutorial');
        $indexBuilder->speedUpRefresh($tutorialIndex);
        $indexBuilder->purgeOldIndices('tutorial');

        $io->success("Your documents have been indexed!");

        return Command::SUCCESS;
    }
}
