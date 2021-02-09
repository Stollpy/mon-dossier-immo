<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class PasswordResettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'mapped' => false,
            'invalid_message' => 'La comfirmation du champ est incorrect !',
            'first_options' =>['label' => 'Votre nouveau mot de passe', 'attr' => ['class' =>'form-control']],
            'second_options' => ['label' => 'Confirmation de votre Mot de passe', 'attr' => ['class' =>'form-control']],
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => 8,
                    'minMessage' => 'Le champ mot de passe doit contenir au moins {{ limit }} caractères.'
                ])
            ]
        ])
    ;
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
