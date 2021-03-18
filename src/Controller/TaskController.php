<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; // należy importować
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * @Route("/task", name = "task.")
 */
class TaskController extends AbstractController
{
    protected $taskRepository;
    protected $userRepository;

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
     */
    public function create(Request $request): Response
    {
        $task = new Task();

        $form = $this->createFormBuilder($task)
            ->add('title', TextType::class, [
                'label' => 'Treść zadania:',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('deadline', DateType::class, [
                'label' => 'Ostateczny termin wykonania zadania:',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('checked', CheckboxType::class, [
                'label' => 'Czy zadanie zostało wykonane?',
                'required' => false,
                'attr' => [
                    'class' => 'form-check'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Dodaj zadanie',
                'attr' =>[
                    'class' => 'btn btn-primary mt-3'
                ]
            ])
            ->getForm();
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $user = $this->getUser();
            $task -> setUserId($user);

            $this->taskRepository->save($task);

            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name = "delete", methods={"DELETE"})
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

    /**
     * @Route("/edit/{id}", name = "edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $id): Response
    {
        $task = $this->taskRepository->find($id);
        if ($this->getUser() !== $task->getUserId())
        {
            return new Response("Nie można edytować nie swoich zadań");
        }

        $form = $this->createFormBuilder($task)
            ->add('title', TextType::class, [
                'label' => 'Treść zadania:',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('deadline', DateType::class, [
                'label' => 'Ostateczny termin wykonania zadania:',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('checked', CheckboxType::class, [
                'label' => 'Czy zadanie zostało wykonane?',
                'required' => false,
                'attr' => [
                    'class' => 'form-check'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Edytuj zadanie',
                'attr' =>[
                    'class' => 'btn btn-primary mt-3'
                ]
            ])
            ->getForm();

        // dump($form);
        // $form = $request->query->all();

        $form->handleRequest($request);
        dump($form);
        dump($task);

        if($form->isSubmitted() && $form->isValid()) {
            $this->taskRepository->update();
            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }




    /**
     * @Route("/editt/{id}", name = "editt", methods={"GET", "POST"})
     */
    public function editt(Request $request, int $id): Response
    {
        $task = $this->taskRepository->find($id);
        if ($this->getUser() !== $task->getUserId())
        {
            return new Response("Nie można edytować nie swoich zadań");
        }

        if ($request->isMethod('POST'))
        {
            $title = $request->request->get('title');
            $deadline = $request->request->get('deadline');
            $checked = $request->request->get('checked');
            // $form = $request->query->all();
            // dump($form);

            /*if($form->isSubmitted() && $form->isValid()) {
                $this->taskRepository->update();
                return $this->redirectToRoute('task.index');
            }*/
            return new Response("Witamy POST");
        }


        // $form->handleRequest($request);
        // dump($form);
        dump($task);


        return $this->render('task/editt.html.twig', [
            'task' => $task
        ]);
    }
}