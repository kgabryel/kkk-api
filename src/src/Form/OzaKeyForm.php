<?php

namespace App\Form;

use App\Config\LengthConfig;
use App\Model\OzaKey;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;

class OzaKeyForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('key', null, [
            'constraints' => [
                new Length([
                    'max' => LengthConfig::OZA_KEY
                ]),
                new Type([
                    'type' => 'string'
                ])
            ],
            'trim' => true
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OzaKey::class,
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
