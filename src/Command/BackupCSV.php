<?php

namespace App\Command;

use App\Service\BackupCSVService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCSV extends Command
{
    protected static $defaultName = 'app:sendBackup';
    private BackupCSVService $backupCSVService;

    public function __construct(BackupCSVService $backupCSVService)
    {
        parent::__construct();
        $this->backupCSVService = $backupCSVService;
    }

    protected function configure()
    {
        $this->setDescription('Sends email with all tasks')
        ->setHelp('This command sends email with all tasks from database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->backupCSVService->sendCSV();

        $message = sprintf("Mail wysłany");

        $output->writeln($message);
        return 0;
    }
}