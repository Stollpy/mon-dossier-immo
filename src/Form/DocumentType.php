<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Repository\ProfilModelDataRepository;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{

    public function __construct(ProfilModelDataRepository $profilModelDataRepository, Security $security){
        $this->profilModelData = $profilModelDataRepository;
        $this->security = $security;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $types = [
            "file" => FileType::class,
        ];
            
            $models = $this->profilModelData->getModelByProfilAndCategory($options['data_profile'], $options["data_category"]);
            
            foreach($models as $model){
                $code = $model->getCode();
                $type = $model->getType();
                $label = $model->getLabel();
                
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            "data_category" => null,
            "data_profile" => null,
        ]);
    }
}
