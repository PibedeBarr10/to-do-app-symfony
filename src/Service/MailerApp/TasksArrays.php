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

        $tasks_array = [];
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
            $tasks_array[] = $array_temp;
        }
        $email_data = array(
            'Wszystkie zadania' => count($tasks),
            'Zadania utworzone w ostatnim tygodniu' => $tasks_last_created,
            'Zadania po terminie' => $tasks_after_deadline
        );

        return [$tasks_array, $email_data];
    }
}