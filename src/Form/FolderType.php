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
        $user = $options['user'];
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Partagé ce dossier avec d\'autres utilisateurs',
                'required' => false,
            ])
            ->add('quizzes', EntityType::class, [
                'class' => Quiz::class,
                'choice_label' => 'title',
                'multiple' => true,
                'query_builder' => function (QuizRepository $quizRepository) use ($user) {
                return $quizRepository->createQueryBuilder('q')
                    ->where('q.author = :user')
                    ->setParameter('user', $user)
                    ->orderBy('q.title', 'ASC');

                }
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
