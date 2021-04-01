<?php


namespace App\Controller\Invitations;


use App\Repository\InvitationTokenRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OpenInvitationController extends AbstractController
{
    private InvitationTokenRepository $invitationTokenRepository;

    public function __construct(InvitationTokenRepository $invitationTokenRepository)
    {
        $this->invitationTokenRepository = $invitationTokenRepository;
    }

    /**
     * @Route("/invitation/{token}", name="invitation", methods={"GET"})
     */
    public function invitation(string $token): Response
    {
        $invitation = $this->invitationTokenRepository->findOneBy([
            'token' => $token
        ]);

        if (!$invitation) {
            $this->addFlash('danger', 'Nie dostałeś zaproszenia!');
            return $this->redirectToRoute('app_login');
        }

        $expiryTime = $invitation->getExpiryDate();
        if ($expiryTime < new DateTime('now')) {
            $this->addFlash('danger', 'Zaproszenie wygasło!');
            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('success', 'Witamy z zaproszenia!');
        return $this->redirectToRoute('register');
    }
}