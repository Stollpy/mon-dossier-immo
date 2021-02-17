<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Repository\ProfilModelDataRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class IdentityType extends AbstractType
{

    public function __construct(ProfilModelDataRepository $profilModelDataRepository, Security $security){
        $this->profilModelData = $profilModelDataRepository;
        $this->security = $security;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $individual = $this->security->getUser()->getIndividual();
        $profils = $this->profilModelData->getModelByIndividual($individual);//$options['data_repository'];
       
        foreach ($profils as $profil){

            $code = $profil->getCode();
            $type = $profil->getType();
            $label = $profil->getLabel();

            $types = [
                "text" => TextType::class,
                "date" => DateType::class,
                "tel" => TelType::class,
            ];

            $codes = [];
            if(!in_array($code, $codes)){
                array_push($codes, $code);
                if(array_key_exists($type, $types)){
                    $builder
                    ->add($code, $types[$type],[
                        'label' => $label,
                        'required' => true,
                        'attr' => ['class' => 'form-control'],
                        'constraints' => [
                            new NotBlank()
                        ]
                    ]);
                }else{
                    return null;
                }
            }



        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            // "data_repository" => $this->profilModelData->getModelByIndividual(),
        ]);
    }
}
