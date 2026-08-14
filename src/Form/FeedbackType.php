<?php

namespace App\Form;

use App\Entity\Feedback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Signaler un bug' => 'bug',
                    'Suggérer une idée' => 'idea'
                ],
                'attr' => [
                    'class' => 'input input-bordered w-full'
                ],
                'label_attr' => [
                    'class' => 'label font-bold'
                ],
                'required' => true
            ])
            ->add('title',TextType::class,[
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Problème sur les quiz publics ...',
                    'class' => 'input input-bordered w-full'
                ],
                'label_attr' => [
                    'class' => 'label font-bold'
                ],
                'required' => true
            ])
            ->add('description', TextareaType::class,[
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Décrivez votre problème ou votre suggestion en détail ...',
                    'class' => 'textarea textarea-bordered h-32 w-full'
                ],
                'label_attr' => [
                    'class' => 'label font-bold'
                ],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
