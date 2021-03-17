<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

/**
 * @Route("/task", name = "task.")
 */
class TaskController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(TaskRepository $taskRepository, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id = $user->getId();

        $tasks = $taskRepository->findUserTasks($id);

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

    /**
     * @Route("/csv", name = "csv")
     */
    public function sendCSV(MailerInterface $mailer)
    {
        $file = fopen('tasks.csv', 'w');

        $tasks = $this->getDoctrine()->getRepository(Task::class)->findAll();
        
        foreach ($tasks as $task)
        {
            $id = $task->getId();
            $title = $task->getTitle();

            $deadline = $task->getDeadline();
            $date = $deadline->format('d-m-Y');

            $user = $task->getUserId();
            $userid = $user->getId();

            $checked = $task->getChecked();

            $array =[$id, $title, $date, $userid, $checked];
            // dump($array);

            fputcsv($file, $array);
        }

        fclose($file);

        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            ->subject('Codzienny backup zadań')
            ->html('<p>W załączniku spis wszystkich zadań znajdujących się w bazie danych.</p>')
            ->attachFromPath('tasks.csv');

        $mailer->send($email);


        return new Response('Mail wysłany!');
    }
}