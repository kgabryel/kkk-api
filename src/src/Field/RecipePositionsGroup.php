<?php

namespace App\Field;

use App\Model\RecipePosition as RecipePositionModel;
use App\Model\RecipePositionsGroup as Model;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Context\ExecutionContext;

class RecipePositionsGroup extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', null, [
            'constraints' => [
                new Length([
                    'max' => 255
                ])
            ]
        ])
            ->add(
                'positions',
                CollectionType::class,
                [
                    'entry_type' => RecipePosition::class,
                    'allow_add' => true,
                    'constraints' => [
                        new Callback(function ($value, ExecutionContext $context) {
                            /** @var RecipePositionModel[] $positions */
                            $positions = $context->getValue();
                            foreach ($positions as $position) {
                                if ($position->getRecipe() === null && $position->getIngredient() === null) {
                                    $context->buildViolation('')->addViolation();
                                    break;
                                }
                            }
                        })
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Model::class
        ]);
    }
}
