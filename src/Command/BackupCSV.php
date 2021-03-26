<?php

namespace App\Command;

use App\Service\Mailer;
use App\Repository\TaskRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCSV extends Command
{
    protected static $defaultName = 'app:sendBackup';
    private TaskRepository $taskRepository;
    private Mailer $mailer;

    public function __construct(TaskRepository $taskRepository, Mailer $mailer)
    {
        parent::__construct();
        $this->taskRepository = $taskRepository;
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this->setDescription('Sends email with all tasks')
        ->setHelp('This command sends email with all tasks from database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->sendCSV();

        $message = sprintf("Mail wysłany");

        $output->writeln($message);
        return 0;
    }

    public function sendCSV(): void
    {
        $file = fopen('tasks.csv', 'w');
        $tasks = $this->taskRepository->findAll();

        foreach ($tasks as $task)
        {
            fputcsv($file, [
                $task->getId(),
                $task->getTitle(),
                $task->getDeadline()->format('d-m-Y'),
                $task->getUserId()->getId(),
                $task->getChecked()
            ]);
        }

        fclose($file);
        $this->mailer->sendMail(
            'hello@example.com',
            'you@example.com',
            'Codzienny backup zadań',
            'command/backup.html.twig',
            'tasks.csv'
        );
    }
}