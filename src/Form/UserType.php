<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'input input-bordered w-full max-w-xs mt-2',
                ],
                'row_attr' => [
                    'class' => 'form-control w-full max-w-xs mb-4'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'input input-bordered w-full max-w-xs mt-2',
                ],
                'row_attr' => [
                    'class' => 'form-control w-full max-w-xs mb-4'
                ]
            ])
            ->add('email', null, [
                'attr' => ['class' => 'input input-bordered w-full max-w-xs mt-2'],
                'row_attr' => ['class' => 'form-control w-full max-w-xs mb-4']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
