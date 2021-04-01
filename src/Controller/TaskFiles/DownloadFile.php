<?php


namespace App\Controller\TaskFiles;


use App\Controller\TaskController;
use App\Repository\AttachmentRepository;
use App\Repository\TaskRepository;
use App\Service\FileManagement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class DownloadFile extends TaskController
{
    public function __construct(
        TaskRepository $taskRepository,
        AttachmentRepository $attachmentRepository,
        FileManagement $fileManagement
    ) {
        parent::__construct(
            $taskRepository,
            $attachmentRepository,
            $fileManagement
        );
    }

    /**
     * @Route("/task/download/{id}/{filename}", name="task.download", methods={"GET"})
     */
    public function download(int $id, string $filename): Response
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

        if ($this->getUser() !== $task->getUserId() || $task !== $taskfile->getTask()) {
            $this->addFlash('danger', 'Nie jesteś właścicielem tego pliku');
            return $this->redirectToRoute('task.index');
        }

        $file = $this->fileManagement->download($filename);

        if (!$file) {
            $this->addFlash('danger', 'Nie znaleziono takiego pliku');
            return $this->redirectToRoute('task.edit', [
                'id' => $id
            ]);
        }

        $file->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $taskfile->getName()
        );
        return $file;
    }
}