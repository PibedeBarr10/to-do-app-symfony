<?php

namespace App\Command;

use App\Service\Mailer;
// use App\Entity\Task;
use App\Repository\TaskRepository;
// use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCSV extends Command
{
    protected static $defaultName = 'app:sendBackup';
    protected TaskRepository $taskRepository;
    protected Mailer $mailer;

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
        return 0;   // Return value of "App\Command\BackupCSV::execute()" must be of the type int, "null" returned.
    }

    public function sendCSV()
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
            '<p>W załączniku znajduje się codzienny backup zadań</p>',
            'tasks.csv'
        );
    }
}