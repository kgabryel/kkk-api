<?php

namespace App\Field;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Model\RecipePosition as Model;
use App\Repository\IngredientRepository;
use App\Repository\RecipeRepository;
use App\Service\UserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RecipePosition extends AbstractType
{
    private array $ingredients;
    private array $recipes;

    public function __construct(
        IngredientRepository $ingredientRepository,
        UserService $userService,
        RecipeRepository $recipeRepository
    ) {
        $user = $userService->getUser();
        $this->ingredients = $ingredientRepository->findForUser($user);
        $this->recipes = $recipeRepository->findForUser($user);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('amount', null, [
            'constraints' => [
                new GreaterThan([
                    'value' => 0
                ])
            ]
        ])
            ->add('measure', null, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 100
                    ])
                ]
            ])
            ->add('ingredient', EntityType::class, [
                'choices' => $this->ingredients,
                'class' => Ingredient::class
            ])
            ->add('recipe', EntityType::class, [
                'choices' => $this->recipes,
                'class' => Recipe::class
            ])
            ->add('additional', CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Model::class
        ]);
    }
}
