<?php


namespace App\Controller\Admin;


use App\Entity\User;
use App\Form\SendRequestFormType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SendRequest extends AbstractController
{
    private UserRepository $userRepository;
    private TaskRepository $taskRepository;

    public function __construct(UserRepository $userRepository, TaskRepository $taskRepository)
    {
        $this->userRepository = $userRepository;
        $this->taskRepository = $taskRepository;
    }

    /**
     * @Route("/admin/sendReport", name="sendReport", methods={"GET", "POST"})
     */
    public function sendReport(Request $request): Response
    {
        $form = $this->createForm(SendRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $user = $this->userRepository->findOneBy([
                'email' => $email
            ]);

            if (!$user) {
                $this->addFlash('error', 'Brak takiego maila w bazie danych');
                return $this->redirectToRoute('sendRequest');
            }

            $content = $this->sendRequest($user);

            return new Response($content);
            /*$this->addFlash('success', $content);
            return $this->redirectToRoute('dashboard');*/
        }

        return $this->render('admin/sendRequest.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/sendReports", name="sendReports", methods={"GET"})
     */
    public function sendReports(): Response
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $user)
        {
            $this->sendRequest($user);
        }

        $this->addFlash('success', 'Wysłano raporty');
        return $this->redirectToRoute('dashboard');
    }

    private function sendRequest(User $user)
    {
        [$tasks_array, $body] = $this->getTasksArrays($user->getId());

        $httpClient = HttpClient::create([
            'auth_basic' => [
                $this->getUser()->getUsername(),
                $this->getUser()->getPassword()
            ]
        ]);
        $response = $httpClient->request('POST',
            'http://www.mailer-app.com/sendMail',
            [
                'json' => [
                    'email' => $user->getUsername(),
                    'body' => $body,
                    'file_data' => $tasks_array
                ]
            ]
        );

        /*
        if (200 !== $response->getStatusCode()) {
            $this->addFlash('error', 'Błąd');
            return $this->redirectToRoute('sendRequest');
        }
        */

        $content = $response->getContent();

        return $content;
    }

    private function getTasksArrays(int $id)
    {
        $tasks = $this->taskRepository->findUserTasks($id);

        $now = new DateTime();

        $array = [];
        $body = ['Informacje:'];
        $tasks_after_deadline = 0;
        $tasks_last_created = 0;
        foreach ($tasks as $task)
        {
            [$after_deadline, $last_created] = $this->analizeTask($now, $task->getDeadline(), $task->getCreationDate());
            $tasks_after_deadline += $after_deadline;
            $tasks_last_created += $last_created;

            $array_temp = array(
                'title' => $task->getTitle(),
                'deadline' => $task->getDeadline()->format('d-m-Y'),
                'checked' => $task->getChecked(),
                'creation_date' => $task->getCreationDate()->format('d-m-Y')
            );
            $array[] = $array_temp;
        }

        $body[] = 'Ilość posiadanych zadań: '.count($tasks);
        $body[] = 'Ilość zadań utworzonych przez ostatni tydzień: '.$tasks_last_created;
        $body[] = 'Ilość zadań po terminie: '.$tasks_after_deadline;

        return [$array, $body];
    }

    private function analizeTask(DateTime $now, DateTime $deadline, DateTime $creationDate)
    {
        $after_deadline = 0;
        $last_created = 0;

        $now->setTime(00, 00, 00);
        $deadline->setTime(00, 00, 00);
        $creationDate->setTime(00, 00, 00);

        if ($now > $deadline) {
            $after_deadline = 1;
        }
        if (date_diff($now, $creationDate)->d <= 7) {
            $last_created = 1;
        }

        return [$after_deadline, $last_created];
    }
}