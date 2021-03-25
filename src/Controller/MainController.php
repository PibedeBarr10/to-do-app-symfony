<?php

namespace App\Controller;

use App\Entity\InvitationToken;
use App\Form\EmailFormType;
use App\Repository\InvitationTokenRepository;
use App\Service\Mailer;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    private InvitationTokenRepository $invitationTokenRepository;
    private Mailer $mailer;

    public function __construct(InvitationTokenRepository $invitationTokenRepository, Mailer $mailer)
    {
        $this->invitationTokenRepository = $invitationTokenRepository;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/invite", name="invite", methods={"GET", "POST"})
     */
    public function invite(Request $request): Response
    {
        $form = $this->createForm(EmailFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $invitation = new InvitationToken();

            $token = md5(uniqid(rand(), true));
            $time = new DateTime('now');
            $time->modify('+15 minutes');

            $invitation->setToken($token);
            $invitation->setExpiryDate($time);

            $this->invitationTokenRepository->save($invitation);

            $email = $form->getData()['email'];

            $this->mailer->sendMail(
                'hello@example.com',
                $email,
                'Zaproszenie do rejestracji',
                'invitation/email.html.twig',
                null,
                $token
            );

            $this->addFlash('success', 'Wysłano zaproszenie');

            return $this->redirectToRoute('index');
        }

        return $this->render('invitation/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/invitation/{token}", name="invitation", methods={"GET"})
     */
    public function invitation(string $token): Response
    {
        $invitation = $this->invitationTokenRepository->findBy([
            'token' => $token
        ]);

        if (!$invitation) {
            $this->addFlash('danger', 'Nie dostałeś zaproszenia!');
            return $this->redirectToRoute('app_login');
        }

        $expiryTime = $invitation[0]->getExpiryDate();
        if ($expiryTime < new DateTime('now')) {
            $this->addFlash('danger', 'Zaproszenie wygasło!');
            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('success', 'Witamy z zaproszenia!');
        return $this->redirectToRoute('register');
    }
}
