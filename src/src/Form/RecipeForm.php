<?php

namespace App\Form;

use App\Config\LengthConfig;
use App\Field\RecipePositionsGroup;
use App\Model\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Url;

class RecipeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            null,
            [
                'constraints' => [
                    new NotBlank(),
                    new Type([
                        'type' => 'string'
                    ]),
                    new Length([
                        'max' => LengthConfig::RECIPE
                    ])
                ],
                'trim' => true
            ]
        )
            ->add(
                'description',
                null,
                [
                    'constraints' => [
                        new Type([
                            'type' => 'string'
                        ])
                    ]
                ]
            )
            ->add(
                'url',
                null,
                [
                    'constraints' => [
                        new Url()
                    ]
                ]
            )
            ->add('favourite', CheckboxType::class)
            ->add('public', CheckboxType::class)
            ->add('toDo', CheckboxType::class)
            ->add(
                'portions',
                null,
                [
                    'constraints' => [
                        new GreaterThan([
                            'value' => 0
                        ])
                    ]
                ]
            )
            ->add(
                'tags',
                CollectionType::class,
                [
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                    'entry_options' => [
                        'constraints' => [
                            new NotBlank(),
                            new Length([
                                'max' => LengthConfig::TAG
                            ])
                        ]
                    ]
                ]
            )
            ->add(
                'groups',
                CollectionType::class,
                [
                    'entry_type' => RecipePositionsGroup::class,
                    'allow_add' => true
                ]
            )
            ->add(
                'timers',
                CollectionType::class,
                [
                    'entry_type' => TimerForm::class,
                    'allow_add' => true
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Recipe::class,
                'csrf_protection' => false
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
