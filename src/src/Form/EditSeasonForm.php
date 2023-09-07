<?php

namespace App\Form;

use App\Model\UpdateSeason;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class EditSeasonForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('start', null, [
            'constraints' => [
                new NotBlank(),
                new Type([
                    'type' => 'digit'
                ]),
                new Range([
                    'min' => 1,
                    'max' => 12
                ])
            ]
        ])
            ->add('stop', null, [
                'constraints' => [
                    new NotBlank(),
                    new Type([
                        'type' => 'digit'
                    ]),
                    new Range([
                        'min' => 1,
                        'max' => 12
                    ]),
                    new GreaterThanOrEqual([
                        'propertyPath' => 'parent.all[start].data'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateSeason::class,
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
