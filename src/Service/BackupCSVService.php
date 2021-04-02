<?php


namespace App\Service;

use App\Repository\TaskRepository;

class BackupCSVService
{
    private TaskRepository $taskRepository;
    private Mailer $mailer;

    public function __construct(
        TaskRepository $taskRepository,
        Mailer $mailer,
    ) {
        $this->taskRepository = $taskRepository;
        $this->mailer = $mailer;
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
        $this->mailer->sendMailWithAttachment(
            'you@example.com',
            'Codzienny backup zadań',
            'command/backup.html.twig',
            'tasks.csv'
        );
    }
}