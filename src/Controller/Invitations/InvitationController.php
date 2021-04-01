<?php


namespace App\Controller\Invitations;


use App\Entity\InvitationToken;
use App\Form\EmailFormType;
use App\Repository\InvitationTokenRepository;
use App\Service\Mailer;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{
    private InvitationTokenRepository $invitationTokenRepository;
    private Mailer $mailer;

    public function __construct(InvitationTokenRepository $invitationTokenRepository, Mailer $mailer)
    {
        $this->invitationTokenRepository = $invitationTokenRepository;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/invite", name="invite", methods={"GET", "POST"})
     */
    public function invite(Request $request): Response
    {
        $form = $this->createForm(EmailFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invitation = new InvitationToken();

            $token = md5(uniqid(rand(), true));
            $time = new DateTime('now');
            $time->modify('+15 minutes');

            $invitation->setToken($token);
            $invitation->setExpiryDate($time);

            $this->invitationTokenRepository->save($invitation);

            $email = $form->getData()['email'];

            $this->mailer->sendMail(
                $email,
                'Zaproszenie do rejestracji',
                'invitation/email.html.twig',
                [
                    'token' => $token
                ]
            );

            $this->addFlash('success', 'Wysłano zaproszenie');

            return $this->redirectToRoute('index');
        }

        return $this->render('invitation/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}