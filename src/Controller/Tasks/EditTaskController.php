<?php


namespace App\Controller\Tasks;

use App\Form\TaskEditFormType;
use App\Repository\AttachmentRepository;
use App\Repository\TaskRepository;
use App\Service\FileManagement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditTaskController extends AbstractController
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
     * @Route("/task/edit/{id}", name="task.edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $id): Response
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

        $form = $this->createForm(TaskEditFormType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('attachment')->getData();
            if ($file) {
                [$originalFilename, $unique_name] = $this->fileManagement->upload($file);

                $this->attachmentRepository->add_file(
                    $task,
                    $unique_name,
                    $originalFilename
                );
            }

            $this->taskRepository->save($task);
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task
        ]);
    }
}