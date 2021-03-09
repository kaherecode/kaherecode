<?php

namespace App\Command;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteSpamCommentCommand extends Command
{
    /**
     * @var CommentRepository
     */
    protected $commentRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        CommentRepository $commentRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected static $defaultName = 'app:delete-spam-comment';

    protected function configure()
    {
        $this
            ->setDescription('Delete spam comment older than 15 days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $spams = $this->commentRepository->findOlderSpams(15);
        $spamCount = count($spams);

        foreach ($spams as $spam) {
            $this->entityManager->remove($spam);
        }

        $this->entityManager->flush();

        $io->success("{$spamCount} spam comments have been deleted!");

        return Command::SUCCESS;
    }
}
