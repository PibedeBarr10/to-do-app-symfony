<?php


namespace App\Controller\Admin;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BackupCSVService;

class SendBackup extends AbstractController
{
    private BackupCSVService $backupCSVService;

    public function __construct(BackupCSVService $backupCSVService)
    {
        $this->backupCSVService = $backupCSVService;
    }

    /**
     * @Route("/admin/sendbackup", name="sendBackup", methods={"GET"})
     */
    public function sendBackup(): Response
    {
        $this->backupCSVService->sendCSV();

        $this->addFlash('success', 'Mail wysłany!');
        return $this->redirectToRoute('dashboard');
    }
}