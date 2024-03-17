<?php

namespace App\Form;

use App\Config\LengthConfig;
use App\Model\Ingredient;
use App\Repository\IngredientRepository;
use App\Service\UserService;
use App\Validator\UniqueNameForUser\UniqueNameForUser;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class IngredientForm extends UserForm
{
    private IngredientRepository $ingredientRepository;

    public function __construct(IngredientRepository $ingredientRepository, UserService $userService)
    {
        parent::__construct($userService);
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
                new UniqueNameForUser(
                    $this->ingredientRepository,
                    $this->user,
                    'name',
                    $options['expect']
                )
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
