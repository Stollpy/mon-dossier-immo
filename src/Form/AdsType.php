<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Le titre de votre annonces',
                'required' => true,
                'attr' => ['class' =>'form-control'],
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('category', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Type d\'annonce',
                'choices' => [
                    'À louez' => 'rental',
                    'À vendre' => 'sale'
                ],
                'required' => true,
                'attr' => ['class' =>'form-control'],
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Le prix ( en € )',
                'required' => true,
                'attr' => ['class' =>'form-control'],
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Le contenu de votre annonces',
                'attr' => ['class' =>'form-control'],
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('pictures', FileType::class, [
                'mapped' => false,
                'label' => 'Vos images',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}
