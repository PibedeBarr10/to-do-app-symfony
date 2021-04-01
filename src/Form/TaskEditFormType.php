<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TaskEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('attachment', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Dodaj załącznik:',
                'attr' => [
                    'class' => 'form-control-file',
                    'type' => 'file'
                ],
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                        ],
                        'mimeTypesMessage' => 'Możliwe typy plików: PDF, DOC i DOCX',
                    ])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Edytuj zadanie',
                'attr' =>[
                    'class' => 'btn btn-primary mt-3'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
