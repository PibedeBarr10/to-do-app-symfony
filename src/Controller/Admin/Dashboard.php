<?php


namespace App\Controller\Admin;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Dashboard extends AbstractController
{
    /**
     * @Route("/admin/", name="dashboard", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
}