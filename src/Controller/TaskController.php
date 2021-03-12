<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * @Route("/task", name = "task.")
 */
class TaskController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();
        // dump($tasks);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route("/delete/{task}", name = "delete")
     */
    public function delete(Task $task)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($task);
        $em->flush();

        return $this->redirect($this->generateUrl(route: 'task.index'));
        // return $this->redirectToRoute('task.index');
    }

    /**
     * @Route("/create", name = "create")
     */
    public function create(Request $request)
    {
        // create a new post with title and date
        $task = new Task();

        $form = $this->createFormBuilder($task)
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                    ]
                ])
            ->add('deadline', DateType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Dodaj',
                'attr' =>[
                    'class' => 'btn btn-primary mt-3'
                ]
            ])
            ->getForm();
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            //$task->setTitle("Zadanie");
            //$task->setDeadline(new \DateTime("2021-04-01"));
            $task = $form->getData();
            // entity manager

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            // return $this->redirectToRoute('task.index');
            return $this->redirect($this->generateUrl(route: 'task.index'));
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}