<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\FileRenameFormType;
use App\Form\TaskCreateFormType;
use App\Form\TaskEditFormType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\FileManagement;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/task", name="task.")
 */
class TaskController extends AbstractController
{
    private TaskRepository $taskRepository;
    private UserRepository $userRepository;
    private FileManagement $fileManagement;

    public function __construct(
        TaskRepository $taskRepository,
        UserRepository $userRepository,
        FileManagement $fileManagement
    )
    {
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;
        $this->fileManagement = $fileManagement;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(): Response
    {
        $userId = $this->getUser()->getId();
        $tasks = $this->taskRepository->findUserTasks($userId);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route("/create", name="create", methods={"GET", "POST"})
     */
    public function create(Request $request): Response
    {
        $task = new Task();

        $form = $this->createForm(TaskCreateFormType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $task -> setUserId($user);

            $this->taskRepository->save($task);

            $file = $form->get('attachment')->getData();
            $id = $task->getId();
            if ($file) {
                $originalFilename = $this->fileManagement->upload($id, $file);
                $task->setAttachment($originalFilename);
            }

            $this->taskRepository->update();
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $id): Response
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            $this->addFlash('danger', 'Nie znaleziono takiego zadania');
            return $this->redirectToRoute('task.index');
        }

        if ($this->getUser() !== $task->getUserId())
        {
            $this->addFlash('danger', 'Nie jesteś właścicielem tego zadania');
            return $this->redirectToRoute('task.index');
        }

        $form = $this->createForm(TaskEditFormType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $file = $form->get('attachment')->getData();
            if ($file) {
                $originalFilename = $this->fileManagement->upload($id, $file);
                $task->setAttachment($originalFilename);
            }

            $this->taskRepository->update();
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(int $id): Response
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            $this->addFlash('danger', 'Nie znaleziono takiego zadania');
            return $this->redirectToRoute('task.index');
        }

        if ($this->getUser() !== $task->getUserId())
        {
            $this->addFlash('danger', 'Nie jesteś właścicielem tego zadania');
            return $this->redirectToRoute('task.index');
        }

        $this->delete_file($id);
        $this->taskRepository->remove($task);
        return $this->redirectToRoute('task.index');
    }

    /**
     * @Route("/download/{id}/{filename}", name="download", methods={"GET"})
     */
    public function download(int $id, string $filename): Response
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            $this->addFlash('danger', 'Nie znaleziono takiego zadania');
            return $this->redirectToRoute('task.index');
        }

        if ($this->getUser() !== $task->getUserId())
        {
            $this->addFlash('danger', 'Nie jesteś właścicielem tego pliku');
            return $this->redirectToRoute('task.index');
        }

        $path = $this->getParameter('uploads_dir');
        $file = new File($path . $id.'/'. $filename);

        if (!$file) {
            $this->addFlash('danger', 'Nie znaleziono takiego pliku');
            return $this->redirectToRoute('task.index');
        }

        return $this->file($file);
    }

    /**
     * @Route("/delete_file/{id}", name="delete_file", methods={"DELETE"})
     */
    public function delete_file(int $id): Response
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            $this->addFlash('danger', 'Nie znaleziono takiego zadania');
            return $this->redirectToRoute('task.index');
        }

        if ($this->getUser() !== $task->getUserId())
        {
            $this->addFlash('danger', 'Nie jesteś właścicielem tego zadania');
            return $this->redirectToRoute('task.index');
        }

        $task->setAttachment(null);

        $this->taskRepository->update();
        $this->fileManagement->delete($id);

        return $this->redirectToRoute('task.index');
    }

    /**
     * @Route("/rename_file/{id}/{filename}", name="rename_file", methods={"GET", "POST"})
     */
    public function rename_file(Request $request, int $id, string $filename): Response
    {
        $task = $this->taskRepository->find($id);
        $oldFilename = $task->getAttachment();

        if (!$task) {
            $this->addFlash('danger', 'Nie znaleziono takiego zadania');
            return $this->redirectToRoute('task.index');
        }

        if ($this->getUser() !== $task->getUserId())
        {
            $this->addFlash('danger', 'Nie jesteś właścicielem tego zadania');
            return $this->redirectToRoute('task.index');
        }

        if (!$oldFilename) {
            $this->addFlash('danger', 'To zadanie nie ma pliku');
            return $this->redirectToRoute('task.index');
        }

        $form = $this->createForm(FileRenameFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newFilename = $this->fileManagement->rename($id, $oldFilename, $form->get('name')->getData());

            $task->setAttachment($newFilename);
            $this->taskRepository->update();

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