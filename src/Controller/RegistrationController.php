<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => [
                    'label' => 'Hasło:',
                    'attr' => ['class' => 'form-control']
                ],
                'second_options' => [
                    'label' => 'Powtórz hasło:',
                    'attr' => ['class' => 'form-control']
                ]
            ])
            ->add('register', SubmitType::class, [
                'label' => 'Zarejestruj się',
                'attr' =>[
                    'class' => 'btn btn-primary mt-3'
                ]
            ])
            ->getForm();
        
        $form -> handleRequest($request);

        if ($form -> isSubmitted())
        {
            $data = $form -> getData();

            $user = new User();
            $user -> setEmail($data['email']);
            $user -> setPassword(
                $passwordEncoder -> encodePassword($user, $data['password'])
            );
            
            $em = $this->getDoctrine()->getManager();

            $em -> persist($user);
            $em -> flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
