<?php


namespace App\Service\MailerApp;

use App\Repository\TaskRepository;

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

        $body = ['Informacje:'];
        $tasks_after_deadline = 0;
        $tasks_last_created = 0;
        foreach ($tasks as $task) {
            if ($task->isOutOfDeadline()) {
                $tasks_after_deadline++;
            }
            if ($task->isCreatedInLastXDays(7)) {
                $tasks_last_created++;
            }

            $array_temp = array(
                'title' => $task->getTitle(),
                'deadline' => $task->getDeadline()->format('d-m-Y'),
                'checked' => $task->getChecked() ? 'true' : 'false',
                'creation_date' => $task->getCreationDate()->format('d-m-Y')
            );
            $array[] = $array_temp;
        }

        $body[] = 'Ilość posiadanych zadań: ' . count($tasks);
        $body[] = 'Ilość zadań utworzonych przez ostatni tydzień: ' . $tasks_last_created;
        $body[] = 'Ilość zadań po terminie: ' . $tasks_after_deadline;

        return [$array, $body];
    }
}