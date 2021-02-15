<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Repository\IndividualDataRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class IdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $individualData = new IndividualDataRepository()->findBy(['individual_id' => $id]);
        
        $builder
            ->add('firstname', TextType::class,[
                'label' => 'Prénom',
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ]
            ]) 
            ->add('lastname', TextType::class,[
                'label' => 'Nom',
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

    public function Individual(IndividualDataRepository $individualDataRepository, $id)
    {
        return $individualDataRepository->findBy(['individual_id' => $id]);
    }
}
