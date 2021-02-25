<?php

namespace App\Form;

use App\Entity\Income;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class IncomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de revenues',
                'placeholder' => 'Choissir un type de revenues',
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'choices' => $options['data_type'],
                'attr' => ['class' =>'form-control']
            ])
            ->add('year', ChoiceType::class, [
                'label' => 'Date de revenues',
                'placeholder' => 'Choissir une année',
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'choices' => $this->YearChoice(),
                'attr' => ['class' =>'form-control']
            ])
            ->add('label', TextType::class, [
                'label' => 'Titre du revenu',
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => ['class' =>'form-control', 'placeholder' => 'ex : Fiche de paie Octobre']
            ])         
            ->add('amount', NumberType::class, [
                'label' => 'Montant en Net',
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => ['class' =>'form-control']
            ])
        ;
    }

    public function YearChoice()
    {
        $date = [];
        $now = new \DateTime();
        $last = $now->format('Y') - 2;
            
        for($i = 1; $i <= 3; $i++){
            $date[$last] = $last;
            ++ $last;
        }
        return $date;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Income::class,
            'data_type' => null
        ]);
    }
}
