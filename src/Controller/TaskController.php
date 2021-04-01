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
    protected FileManagement $fileManagement;
    protected AttachmentRepository $attachmentRepository;

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