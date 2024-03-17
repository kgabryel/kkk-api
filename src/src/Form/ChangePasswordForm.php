<?php

namespace App\Form;

use App\Config\LengthConfig;
use App\Model\ChangePassword;
use App\Service\UserService;
use App\Validator\CorrectPassword\CorrectPassword;
use App\Validator\DifferentPassword;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContext;

class ChangePasswordForm extends UserForm
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserService $userService, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($userService);
        $this->passwordEncoder = $passwordEncoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('oldPassword', PasswordType::class, [
            'constraints' => [
                new NotBlank(),
                new Type([
                    'type' => 'string'
                ]),
                new CorrectPassword($this->user, $this->passwordEncoder)
            ]
        ])
            ->add('newPassword', RepeatedType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(),
                    new Type([
                        'type' => 'string'
                    ]),
                    new Length([
                        'max' => LengthConfig::PASSWORD
                    ]),
                    new Callback(function($value, ExecutionContext $context) {
                        $validator = new DifferentPassword($context);
                        $validator->validate($value);
                    })
                ],
                'type' => PasswordType::class
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangePassword::class,
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
