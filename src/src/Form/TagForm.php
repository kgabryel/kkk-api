<?php

namespace App\Form;

use App\Config\LengthConfig;
use App\Model\Tag;
use App\Repository\TagRepository;
use App\Service\UserService;
use App\Validator\UniqueNameForUser\UniqueNameForUser;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class TagForm extends UserForm
{
    private TagRepository $tagRepository;

    public function __construct(TagRepository $tagRepository, UserService $userService)
    {
        parent::__construct($userService);
        $this->tagRepository = $tagRepository;
    }

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
                        'max' => LengthConfig::TAG
                    ]),
                    new UniqueNameForUser(
                        $this->tagRepository,
                        $this->user,
                        'name',
                        $options['expect']
                    )
                ],
                'trim' => true
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Tag::class,
                'expect' => 0,
                'csrf_protection' => false
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
