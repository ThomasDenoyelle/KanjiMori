<?php

namespace App\Form;

use App\Entity\Folder;
use App\Repository\FolderRepository;
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
        $currentUser = $options['user'];
        /** @var Folder|null $currentFolder */
        $currentFolder = $builder->getData();

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'label_attr' => [
                    'class' => 'label font-semibold',
                ],
                'attr' => [
                    'class' => 'input input-bordered w-full',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'label_attr' => [
                    'class' => 'label font-semibold',
                ],
                'attr' => [
                    'class' => 'textarea textarea-bordered w-full',
                    'rows' => 3,
                ],
            ])
            ->add('parent', EntityType::class, [
                'class' => Folder::class,
                'choice_label' => 'title',
                'label' => 'Dossier parent (optionnel)',
                'required' => false,
                'placeholder' => '-- Aucun (Dossier racine) --',
                'query_builder' => function (FolderRepository $repo) use ($currentUser, $currentFolder) {
                    $qb = $repo->createQueryBuilder('f')
                        ->where('f.author = :user')
                        ->setParameter('user', $currentUser)
                        ->orderBy('f.title', 'ASC');

                    if ($currentFolder && $currentFolder->getId()) {
                        $qb->andWhere('f.id != :currentId')
                            ->setParameter('currentId', $currentFolder->getId());
                    }

                    return $qb;
                },
                'label_attr' => [
                    'class' => 'label font-semibold',
                ],
                'attr' => [
                    'class' => 'select select-bordered w-full',
                ],
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Partager ce dossier avec d\'autres utilisateurs',
                'required' => false,
                'label_attr' => [
                    'class' => 'label font-semibold',
                ],
                'attr' => [
                    'class' => 'toggle toggle-primary',
                ],
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
