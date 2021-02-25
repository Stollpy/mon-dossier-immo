<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $form = $builder->add('data', FileType::class, [
                'label' => 'Votre Document',
                'attr' => ['class' =>'form-control'],
                'required' => true,
                'constraints' => [
                        new NotBlank()
                    ]
                ]);
            if($options['data_label'] !== null){
                $form ->add($options['data_label'], TextType::class, [
                    'label' => 'Le titre de votre document',
                    'attr' => ['class' => 'form-control'],
                    'required' => true,
                    'constraints' => [
                        new NotBlank()
                    ]
                ]);
            }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
            'data_label' => null,
        ]);
    }
}
