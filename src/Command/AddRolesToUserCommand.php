<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddRolesToUserCommand extends Command
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    protected static $defaultName = "app:add-user-roles";

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription("Add a roles to a user")
            ->addArgument(
                "username",
                InputArgument::REQUIRED,
                "User's username to which you want to add a roles"
            )
            ->addArgument(
                "roles",
                InputArgument::REQUIRED,
                "The roles you want to add the user"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument("username");
        $roles = $input->getArgument("roles");

        $user = $this->userRepository->findOneByUsername($username);

        if (! $user) {
            $io->error("User {$username} doesn't exists.");

            return Command::FAILURE;
        }

        $user->addRoles($roles);
        $this->entityManager->flush();

        $io->success("The roles {$roles} have been added to user {$username}.");

        return Command::SUCCESS;
    }
}
