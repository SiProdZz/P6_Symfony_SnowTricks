<?php

namespace App\Form;

use App\Entity\Figure;
use App\Entity\Group;
use App\Validator as AppAssert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FigureFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new AppAssert\UniqueSlug(),
                ],
            ])
            ->add('description', TextareaType::class)
            ->add('groupType', EntityType::class, [
                'label' => 'Groupe de la figure',
                'class' => Group::class,
                'choice_label' => 'name',
                'placeholder' => '--- Choisissez un groupe',
            ])
            ->add('illustrations', CollectionType::class, [
                'entry_type' => IllustrationFormType::class,
                'label' => false,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoFormType::class,
                'label' => false,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Figure::class,
        ]);
    }
}
