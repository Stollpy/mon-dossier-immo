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
        $profils = $this->security->getUser()->getIndividual()->getProfiles();

        foreach($profils as $profil){

            $types = [
                "text" => TextType::class,
                "date" => DateType::class,
                "tel" => TelType::class,
            ];
            $code = $profil->getCode();
            
            $models = $this->profilModelData->getModelByProfil($code);

            foreach($models as $model){
                $code = $model->getCode();
                $type = $model->getType();
                $label = $model->getLabel();
    
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
            // End Foreach 
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
