<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskCreateFormType;
use App\Form\TaskEditFormType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; // należy importować
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/task", name = "task.")
 */
class TaskController extends AbstractController
{
    protected TaskRepository $taskRepository;
    protected UserRepository $userRepository;

    public function __construct(TaskRepository $taskRepository, UserRepository $userRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name = "index", methods={"GET"})
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
     * @Route("/create", name = "create", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function create(Request $request): Response
    {
        $task = new Task();

        $form = $this->createForm(TaskCreateFormType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $taskData = $form->getData();

            $user = $this->getUser();
            $taskData -> setUserId($user);

            $this->taskRepository->save($taskData);
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name = "edit", methods={"GET", "POST"})
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function edit(Request $request, int $id): Response
    {
        $task = $this->taskRepository->find($id);

        $form = $this->createForm(TaskEditFormType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $taskData = $form->getData();

            $this->taskRepository->update();
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name = "delete", methods={"DELETE"})
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response
    {
        $task = $this->taskRepository->find($id);

        if ($this->getUser() !== $task->getUserId())
        {
            return new Response("Nie można usuwać nie swoich zadań");
        }
        $this->taskRepository->remove($task);

        // return $this->redirectToRoute('task.index');
        return new Response();
    }
}