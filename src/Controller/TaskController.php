<?php

namespace App\Controller;

use App\Repository\AttachmentRepository;
use App\Repository\TaskRepository;
use App\Service\FileManagement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    protected TaskRepository $taskRepository;
    protected AttachmentRepository $attachmentRepository;
    protected FileManagement $fileManagement;

    public function __construct(
        TaskRepository $taskRepository,
        AttachmentRepository $attachmentRepository,
        FileManagement $fileManagement
    ) {
        $this->taskRepository = $taskRepository;
        $this->attachmentRepository = $attachmentRepository;
        $this->fileManagement = $fileManagement;
    }

    /**
     * @Route("/task/", name="task.index", methods={"GET"})
     */
    public function index(): Response
    {
        $userId = $this->getUser()->getId();
        $tasks = $this->taskRepository->findUserTasks($userId);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }
}