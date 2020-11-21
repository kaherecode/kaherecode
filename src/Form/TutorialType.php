<?php

namespace App\Form;

use App\Entity\Tutorial;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TutorialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('markdownContent', TextareaType::class, ['required' => false])
            ->add(
                'picture',
                FileType::class,
                ['required' => false, 'mapped' => false]
            )
            ->add('description', TextareaType::class, ['required' => false])
            ->add('videoLink', TextType::class, ['required' => false])
            ->add('sourceCodeLink', TextType::class, ['required' => false])
            ->add('demoLink', TextType::class, ['required' => false])
            ->add('categories', TextType::class, ['mapped' => false, 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tutorial::class,
        ]);
    }
}
