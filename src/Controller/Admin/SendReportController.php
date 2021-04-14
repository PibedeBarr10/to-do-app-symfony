<?php


namespace App\Controller\Admin;


use App\Form\SendRequestFormType;
use App\Repository\UserRepository;
use App\Service\MailerApp\SendRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class SendReportController extends AbstractController
{
    private UserRepository $userRepository;
    private SendRequest $sendRequest;

    public function __construct(UserRepository $userRepository, SendRequest $sendRequest)
    {
        $this->userRepository = $userRepository;
        $this->sendRequest = $sendRequest;
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
                $this->addFlash('danger', 'Brak takiego maila w bazie danych');
                return $this->redirectToRoute('sendReport');
            }

            try {
                $message = $this->sendRequest->sendRequest($user);
            } catch (Throwable $exception) {
                $this->addFlash('danger', $exception->getMessage());
                return $this->redirectToRoute('sendReport');
            }

            $this->addFlash('success', $message);
            return $this->redirectToRoute('dashboard');
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
        foreach ($users as $user) {
            try {
                $this->sendRequest->sendRequest($user);
            } catch (Throwable $e) {
                $this->addFlash('danger', 'Błąd w wysyłaniu raportów - kod '.$e->getCode());
                return $this->redirectToRoute('dashboard');
            }
        }

        $this->addFlash('success', 'Wysłano raporty');
        return $this->redirectToRoute('dashboard');
    }
}