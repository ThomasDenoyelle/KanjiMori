<?php

namespace App\Form;

use App\Entity\Folder;
use App\Entity\Quiz;
use App\Entity\User;
use App\Repository\QuizRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FolderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'label_attr' => [
                    'class' => 'label font-semibold'
                ],
                'attr' => [
                    'class' => 'input input-bordered w-full',
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'label_attr' => [
                    'class' => 'label font-semibold'
                ],
                'attr' => [
                    'class' => 'textarea textarea-bordered w-full',
                    'rows' => 3
                ]
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Partagé ce dossier avec d\'autres utilisateurs',
                'required' => false,
                'label_attr' => [
                    'class' => 'label font-semibold'
                ],
                'attr' => [
                    'class' => 'toggle toggle-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Folder::class,
            'user' => null,
        ]);
    }
}
