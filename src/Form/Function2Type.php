<?php

namespace App\Form\Type;

use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
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
                'label' => 'Code Postal :',
                'constraints' => [
                    new Assert\Callback(
                        ['callback' => static function ($data, ExecutionContextInterface $context) {
                            if ((!is_numeric($data)) OR (strlen($data)!=5)) {
                                $context
                                    ->buildViolation("Veuillez entrer un code postal valide, exemple: 33000")
                                    ->addViolation()
                                ;
                            }
                        }]
                    )
                ]
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