<?php

namespace App\Form;

use App\Services\FormHelper;
use Symfony\Component\Form\AbstractType;
use App\Repository\ProfilModelDataRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class IdentityType extends AbstractType
{
    private $profilModelData;
    private $formHelper;

    public function __construct(ProfilModelDataRepository $profilModelDataRepository, FormHelper $formHelper){
        $this->profilModelData = $profilModelDataRepository;
        $this->formHelper = $formHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $models = $this->profilModelData->getModelByProfilAndCategory($options['data_profile'], $options["data_category"]);
         $dataForms = $this->formHelper->dataForm($models);              
        for ($i = 0; $i < count($models); $i++){
            if($dataForms[$i]['code'] !== 'birth_date'){
                $builder
                ->add($dataForms[$i]['code'], $dataForms[$i]['type'], [
                    'label' => $dataForms[$i]['label'],
                    'data' => $dataForms[$i]['data']['data'],
                    'required' => true,
                    'attr' => ['class' => 'form-control input-form', 'data-ind-id' => $dataForms[$i]['data']['id']],
                    'constraints' => [
                        new NotBlank()
                    ]
                ]);
            }else{
                $builder
                ->add($dataForms[$i]['code'], $dataForms[$i]['type'], [
                    'label' => $dataForms[$i]['label'],
                    'data' => $dataForms[$i]['data']['data'],
                    'required' => true,
                    'widget' => 'single_text',
                    'attr' => ['class' => 'form-control input-form', 'data-ind-id' => $dataForms[$i]['data']['id']],
                    'constraints' => [
                        new NotBlank()
                    ]
                ]);
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
