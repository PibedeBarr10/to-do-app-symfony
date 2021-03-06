<?php


namespace App\Controller\Tasks;

use App\Entity\Task;
use App\Form\TaskCreateFormType;
use App\Repository\AttachmentRepository;
use App\Repository\TaskRepository;
use App\Service\FileManagement;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateTaskController extends AbstractController
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
     * @Route("/task/create", name="task.create", methods={"GET", "POST"})
     */
    public function create(Request $request): Response
    {
        $task = new Task();

        $form = $this->createForm(TaskCreateFormType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $task->setUserId($this->getUser());
            $task->setCreationDate(new DateTime());

            $this->taskRepository->save($task);

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

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}