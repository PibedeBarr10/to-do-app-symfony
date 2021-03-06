<?php


namespace App\Controller\TaskFiles;


use App\Repository\AttachmentRepository;
use App\Repository\TaskRepository;
use App\Service\FileManagement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteFileController extends AbstractController
{
    private TaskRepository $taskRepository;
    private FileManagement $fileManagement;
    private AttachmentRepository $attachmentRepository;

    public function __construct(
        TaskRepository $taskRepository,
        FileManagement $fileManagement,
        AttachmentRepository $attachmentRepository
    ) {

        $this->taskRepository = $taskRepository;
        $this->fileManagement = $fileManagement;
        $this->attachmentRepository = $attachmentRepository;
    }

    /**
     * @Route("/task/delete_file/{id}/{filename}", name="task.delete_file", methods={"DELETE"})
     */
    public function delete_file(int $id, string $filename): Response
    {
        $task = $this->taskRepository->find($id);
        $taskfile = $this->attachmentRepository->findOneBy([
            'task' => $task,
            'uniquename' => $filename
        ]);

        if (!$task) {
            $this->addFlash('danger', 'Nie znaleziono takiego zadania');
            return $this->redirectToRoute('task.index');
        }

        if ($this->getUser() !== $task->getUserId()  || $task !== $taskfile->getTask()) {
            $this->addFlash('danger', 'Nie jesteś właścicielem tego zadania');
            return $this->redirectToRoute('task.index');
        }

        $task->removeAttachment($taskfile);
        $this->attachmentRepository->remove($taskfile);

        $this->taskRepository->save($task);
        $this->fileManagement->delete($filename);

        return $this->redirectToRoute('task.index');
    }
}