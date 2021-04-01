<?php


namespace App\Controller\Tasks;

use App\Controller\TaskController;
use DateTime;
use App\Entity\Attachment;
use App\Form\TaskEditFormType;
use App\Repository\AttachmentRepository;
use App\Repository\TaskRepository;
use App\Service\FileManagement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditTask extends TaskController
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

                $attachment = new Attachment();
                $attachment->setTask($task);
                $attachment->setUniqueName($unique_name);
                $attachment->setName($originalFilename);
                $attachment->setCreationDate(new DateTime());

                $this->attachmentRepository->save($attachment);
                $task->addAttachment($attachment);
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