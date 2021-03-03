<?php

namespace App\Command;

use App\Services\CreateProjectMinimaHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProjectMinimaCommand extends Command
{
    protected static $defaultName = 'app:create-project-minima';
    protected static $defaultDescription = 'Command to (re)start the project minimum level';
    protected $createProjectHelper;

    public function __construct(CreateProjectMinimaHelper $createProjectHelper)
    {
        parent::__construct();
        $this->createProjectHelper = $createProjectHelper;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $this->createProjectHelper->createProjectMinima();
        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        // if ($input->getOption('option1')) {
        //     // ...
        // }

        $io->success('Yeah! The project was created !');

        return Command::SUCCESS;
    }
}
