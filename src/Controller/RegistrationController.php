<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/register", name="register", methods={"GET", "POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataForm = $form->getData();
            $duplicate = $this->userRepository->findOneBy(['email' => $dataForm->getEmail()]);

            if (!is_object($duplicate)) {
                $user->setPassword(
                    $passwordEncoder->encodePassword($user, $dataForm->getPassword())
                );

                $user->setRoles(['ROLE_USER']);

                $this->userRepository->save($user);
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
