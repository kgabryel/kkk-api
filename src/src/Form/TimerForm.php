<?php

namespace App\Form;

use App\Config\LengthConfig;
use App\Model\Timer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Type;

class TimerForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            null,
            [
                'constraints' => [
                    new Type([
                        'type' => 'string'
                    ]),
                    new Length([
                        'max' => LengthConfig::TIMER
                    ])
                ],
                'trim' => true
            ]
        )->add(
            'time',
            null,
            [
                'constraints' => [
                    new NotBlank(),
                    new Type([
                        'type' => 'digit'
                    ]),
                    new Positive()
                ]
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Timer::class,
                'csrf_protection' => false
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
