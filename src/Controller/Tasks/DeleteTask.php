<?php


namespace App\Controller\Tasks;


use App\Controller\TaskController;
use App\Repository\AttachmentRepository;
use App\Repository\TaskRepository;
use App\Service\FileManagement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteTask extends TaskController
{
    public function __construct(
        TaskRepository $taskRepository,
        FileManagement $fileManagement,
        AttachmentRepository $attachmentRepository
    ) {
        parent::__construct(
            $taskRepository,
            $fileManagement,
            $attachmentRepository
        );
    }

    /**
     * @Route("/task/delete/{id}", name="task.delete", methods={"DELETE"})
     */
    public function delete(int $id): Response
    {
        $task = $this->taskRepository->find($id);


        if (!$task) {
            $this->addFlash('danger', 'Nie znaleziono takiego zadania');
            return $this->redirectToRoute('task.index');
        }

        if ($this->getUser() !== $task->getUserId()) {
            $this->addFlash('danger', 'Nie jesteś właścicielem tego zadania');
            return $this->redirectToRoute('task.index');
        }

        $taskfiles = $this->attachmentRepository->findBy([
            'task' => $task
        ]);

        if ($taskfiles) {
            foreach ($taskfiles as $file) {
                $task->removeAttachment($file);
                $this->attachmentRepository->remove($file);

                $this->taskRepository->save($task);
                $this->fileManagement->delete($file->getUniqueName());
            }
        }

        $this->taskRepository->remove($task);
        return $this->redirectToRoute('task.index');
    }
}