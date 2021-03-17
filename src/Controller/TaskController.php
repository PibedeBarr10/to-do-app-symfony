<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id = $user->getId();
        // dump($id);

        $tasks = $taskRepository->findUserTasks($id);
        // dump($tasks);
        
        /*dump($tasks);
        foreach ($tasks as $task) {
            dump($task);
        }*/

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route("/create", name = "create")
     */
    public function create(Request $request)
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

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $task -> setUserId($user);

            // entity manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
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

        // return $this->redirect($this->generateUrl(route: 'task.index'));
        return $this->redirectToRoute('task.index');
    }

    /**
     * @Route("/edit/{id}", name = "edit")
     */
    public function edit(Request $request, $id)
    {
        $task = new Task();
        $task = $this->getDoctrine()->getRepository(Task::class)->find($id);

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
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('task.index');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}