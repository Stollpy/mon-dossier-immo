<?php

namespace App\Command;

use App\Services\DeleteGhostAccountService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteGhostAccountCommand extends Command
{
    protected static $defaultName = 'app:delete-ghost-account';
    protected static $defaultDescription = 'This command is used to remove ghost accounts (accounts that have not been verified by e-mail) from your DB.';
    protected $ServiceDelete;

    public function __construct(DeleteGhostAccountService $ServiceDelete)
    {
        $this->ServiceDelete = $ServiceDelete;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->ServiceDelete->DeleteGhostAccount();

        $io->success('All ghost accounts have been deleted! ');

        return Command::SUCCESS;
    }
}
