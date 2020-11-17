<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstimatePriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cityCode', IntegerType::class, [
                'label' => 'Code postal'
            ])
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Maison' => 1,
                    'Appartement' => 2,
                    'Dépendance' => 3,
                    'Local industriel. commercial ou assimilé' => 4
                ],
                'required' => false,
            ])
            ->add('section', TextType::class, [
                'required' => false
            ])
            ->add('surface', IntegerType::class, [
                'label' => 'Surface du terrain',
                'required' => false
            ])
            ->add('area', IntegerType::class, [
                'label' => 'Surface habitable',
                'required' => false,
            ])
            ->add('numberRoom', ChoiceType::class, [
                'label' => 'Nombre de pièce',
                'required' => false,
                'choices'  => [
                    'Aucune' => 0,
                    'T1' => 1,
                    'T2' => 2,
                    'T3' => 3,
                    'T4' => 4,
                    'T5' => 5
                ],
            ])
            ->add('modern', CheckboxType::class, [
                'required' => false
            ])
            ->add('transport', CheckboxType::class, [
                'required' => false,
                'label' => 'Proche des transports (5 minutes à pied)'
            ])
            ->add('shops', CheckboxType::class, [
                'required' => false,
                'label' => 'Proche des commerces (5 minutes à pied)'
            ])
            ->add('travaux', CheckboxType::class, [
                'required' => false,
                'label' => 'À rénover'
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
