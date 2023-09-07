<?php

namespace App\Form;

use App\Config\LengthConfig;
use App\Model\Ingredient;
use App\Repository\IngredientRepository;
use App\Validator\UniqueNameForUser\UniqueNameForUser;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class IngredientForm extends UserForm
{
    private IngredientRepository $ingredientRepository;

    public function __construct(IngredientRepository $ingredientRepository, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($tokenStorage);
        $this->ingredientRepository = $ingredientRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', null, [
            'constraints' => [
                new NotBlank(),
                new Type([
                    'type' => 'string'
                ]),
                new Length([
                    'max' => LengthConfig::INGREDIENT
                ]),
                new UniqueNameForUser([
                    UniqueNameForUser::REPOSITORY_OPTION => $this->ingredientRepository,
                    UniqueNameForUser::USER_OPTION => $this->user,
                    UniqueNameForUser::COLUMN_OPTION => 'name',
                    UniqueNameForUser::EXPECT_OPTION => $options['expect']
                ])
            ],
            'trim' => true
        ])
            ->add('available', CheckboxType::class)
            ->add('ozaId', null, [
                'constraints' => [
                    new Type([
                        'type' => 'digit'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
            'expect' => 0,
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
