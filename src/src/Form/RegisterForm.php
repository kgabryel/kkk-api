<?php

namespace App\Form;

use App\Config\LengthConfig;
use App\Model\User;
use App\Repository\UserRepository;
use App\Validator\UniqueEmail\UniqueEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class RegisterForm extends AbstractType
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'email',
            null,
            [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => LengthConfig::EMAIL
                    ]),
                    new Email(),
                    new UniqueEmail(
                        [
                            UniqueEmail::REPOSITORY_OPTION => $this->repository
                        ]
                    )
                ]
            ]
        )
            ->add('password', RepeatedType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(),
                    new Type([
                        'type' => 'string'
                    ]),
                    new Length([
                        'max' => LengthConfig::PASSWORD
                    ])
                ],
                'type' => PasswordType::class
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'csrf_protection' => false
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
