<?php

namespace App\Command;

use App\Repository\TutorialRepository;
use App\Service\GoogleAnalyticsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetTutorialsPageViewsCommand extends Command
{
    /**
     * @var TutorialRepository
     */
    protected $tutorialRepository;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        TutorialRepository $tutorialRepository,
        UrlGeneratorInterface $router,
        EntityManagerInterface $entityManager
    ) {
        $this->tutorialRepository = $tutorialRepository;
        $this->router = $router;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected static $defaultName = "app:get-tutorials-page-views";

    protected function configure()
    {
        $this
            ->setDescription(
                "Get the page views of published tutorials from Google Analytics"
            )
            ->addArgument(
                "tutorialId",
                InputArgument::OPTIONAL,
                "The id of the tutorial you want to get page views"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tutorialId = $input->getArgument("tutorialId");
        $tutorials = [];

        if ($tutorialId) {
            $tutorial = $this->tutorialRepository->find($tutorialId);
            if ($tutorial && $tutorial->getIsPublished()) {
                $io->note(
                    sprintf(
                        "Getting page views analytics for tutorial: %s",
                        $tutorialId
                    )
                );

                $tutorials[] = $tutorial;
            } else {
                $io->error("Tutorial with id = {$tutorialId} not found.");

                return Command::FAILURE;
            }
        } else {
            $io->note("Getting page views analytics for all published tutorials.");

            $tutorials = $this->tutorialRepository->findBy(['isPublished' => true]);
        }

        $analytics = new GoogleAnalyticsService($_ENV['GOOGLE_VIEW_ID']);
        $pageViews = 0;
        array_map(
            function ($t) use ($analytics, &$pageViews) {
                $path = $this->router->generate(
                    "tutorial_view",
                    ["slug" => $t->getSlug()]
                );
                $views = $analytics->getPathPageViews($path, "2019-01-01");
                $t->setViews($views);
                $pageViews += $views;
            },
            $tutorials
        );

        $this->entityManager->flush();

        $io->success(
            "Page views updated successfully!
            You have a total {$pageViews} page views."
        );

        return Command::SUCCESS;
    }
}
