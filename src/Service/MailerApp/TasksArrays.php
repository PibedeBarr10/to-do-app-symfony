<?php


namespace App\Service\MailerApp;

use App\Repository\TaskRepository;
use DateTime;

class TasksArrays
{
    private TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getTasksArrays(int $id): array
    {
        $tasks = $this->taskRepository->findUserTasks($id);

        $now = new DateTime();

        $array = [];
        $body = ['Informacje:'];
        $tasks_after_deadline = 0;
        $tasks_last_created = 0;
        foreach ($tasks as $task)
        {
            [$after_deadline, $last_created] = $this->analizeTask($now, $task->getDeadline(), $task->getCreationDate());
            $tasks_after_deadline += $after_deadline;
            $tasks_last_created += $last_created;

            $array_temp = array(
                'title' => $task->getTitle(),
                'deadline' => $task->getDeadline()->format('d-m-Y'),
                'checked' => $task->getChecked(),
                'creation_date' => $task->getCreationDate()->format('d-m-Y')
            );
            $array[] = $array_temp;
        }

        $body[] = 'Ilość posiadanych zadań: '.count($tasks);
        $body[] = 'Ilość zadań utworzonych przez ostatni tydzień: '.$tasks_last_created;
        $body[] = 'Ilość zadań po terminie: '.$tasks_after_deadline;

        return [$array, $body];
    }

    private function analizeTask(
        DateTime $now,
        DateTime $deadline,
        DateTime $creationDate
    ): array
    {
        $after_deadline = 0;
        $last_created = 0;

        $now->setTime(00, 00, 00);
        $deadline->setTime(00, 00, 00);
        $creationDate->setTime(00, 00, 00);

        if ($now > $deadline) {
            $after_deadline = 1;
        }
        if (date_diff($now, $creationDate)->d <= 7) {
            $last_created = 1;
        }

        return [$after_deadline, $last_created];
    }
}