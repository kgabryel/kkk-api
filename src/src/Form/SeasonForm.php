<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Model\Season;
use App\Repository\IngredientRepository;
use App\Service\UserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class SeasonForm extends UserForm
{
    private array $ingredients;

    public function __construct(IngredientRepository $ingredientRepository, UserService $userService)
    {
        parent::__construct($userService);
        $this->ingredients = $ingredientRepository->findIngredientsWithoutSeason($this->user);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('ingredient', EntityType::class, [
            'choices' => $this->ingredients,
            'class' => Ingredient::class,
            'constraints' => [
                new NotBlank()
            ]
        ])
            ->add('start', null, [
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
            'data_class' => Season::class,
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
