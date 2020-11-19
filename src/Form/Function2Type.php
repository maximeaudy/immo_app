<?php

namespace App\Form\Type;

use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Function2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('budget', IntegerType::class,[
                'label' => 'Budget :'
            ])
            ->add('code_postal', IntegerType::class,[
                'label' => 'Code Postal :'
            ])
            ->add('type',ChoiceType::class,[
                'choices' =>[
                    'Appartement' => '2',
                    'Maison' => '1'
                ]
                ,'label' => 'Type de Bien :'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Estimer'
            ])
            ->getForm()
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}