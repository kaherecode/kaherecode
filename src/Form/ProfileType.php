<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, ['disabled' => true])
            ->add('username', TextType::class, ['disabled' => true])
            ->add('fullName', TextType::class)
            ->add('avatar', FileType::class, ['required' => false])
            ->add('bio', TextareaType::class, ['required' => false])
            ->add('github', TextType::class, ['required' => false])
            ->add('twitter', TextType::class, ['required' => false])
            ->add('linkedin', TextType::class, ['required' => false])
            ->add('website', TextType::class, ['required' => false])
            // the user current password for confirmation
            ->add('currentPassword', PasswordType::class, ['mapped' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
