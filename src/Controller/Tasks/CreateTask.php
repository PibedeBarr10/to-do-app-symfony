<?php


namespace App\Controller\Tasks;

use App\Controller\TaskController;
use DateTime;
use App\Entity\Attachment;
use App\Entity\Task;
use App\Form\TaskCreateFormType;
use App\Repository\AttachmentRepository;
use App\Repository\TaskRepository;
use App\Service\FileManagement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateTask extends TaskController
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
     * @Route("/task/create", name="task.create", methods={"GET", "POST"})
     */
    public function create(Request $request): Response
    {
        $task = new Task();

        $form = $this->createForm(TaskCreateFormType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $task->setUserId($this->getUser());

            $this->taskRepository->save($task);

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

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}