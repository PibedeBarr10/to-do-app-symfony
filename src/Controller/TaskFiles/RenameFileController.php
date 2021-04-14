<?php


namespace App\Controller\TaskFiles;


use App\Form\FileRenameFormType;
use App\Repository\AttachmentRepository;
use App\Repository\TaskRepository;
use App\Service\FileManagement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RenameFileController extends AbstractController
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
     * @Route("/task/rename_file/{id}/{filename}", name="task.rename_file", methods={"GET", "POST"})
     */
    public function rename_file(Request $request, int $id, string $filename): Response
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

        if ($this->getUser() !== $task->getUserId()) {
            $this->addFlash('danger', 'Nie jesteś właścicielem tego zadania');
            return $this->redirectToRoute('task.index');
        }

        if ($task->getAttachment() === null) {
            $this->addFlash('danger', 'To zadanie nie ma pliku');
            return $this->redirectToRoute('task.edit', [
                'id' => $id
            ]);
        }

        $oldFilename = $taskfile->getName();

        $form = $this->createForm(FileRenameFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newFilename = $form->get('name')->getData();

            if (!$newFilename) {
                $this->addFlash('danger', 'Nie podano nowej nazwy pliku');
                return $this->redirectToRoute('task.edit', [
                    'id' => $id
                ]);
            }

            $extension = pathinfo($oldFilename)['extension'];
            $taskfile->setName($newFilename.'.'.$extension);
            $this->attachmentRepository->save($taskfile);

            return $this->redirectToRoute('task.edit', [
                'id' => $id
            ]);
        }

        return $this->render('task/rename.html.twig', [
            'form' => $form->createView(),
            'oldFilename' => $oldFilename
        ]);
    }
}