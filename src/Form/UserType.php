<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre e-mail',
                'constraints' => [
                   new NotBlank()
                ],
                'required' => true,
                'attr' => ['class' =>'form-control']
            ])
            ->add('profiles', ChoiceType::class, [
                'mapped' => false,
                'choices' =>[
                    'Locataire' => 'tenant',
                    'Vendeur/Loueur' => 'seller'
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Votre profiles'
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'La comfirmation du mot de passe est incorrect',
                'required' => true,
                'first_options' => ['label' => 'Mot de passe', 'attr' => ['class' =>'form-control']],
                'second_options' => ['label' => 'Confirmation du mot de passe', 'attr' => ['class' =>'form-control']],
                'constraints' => [
                        new NotBlank(),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
                        ])
                    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
