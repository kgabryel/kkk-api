<?php

namespace App\Form;

use App\Config\LengthConfig;
use App\Model\Tag;
use App\Repository\TagRepository;
use App\Validator\UniqueNameForUser\UniqueNameForUser;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class TagForm extends UserForm
{
    private TagRepository $tagRepository;

    public function __construct(TagRepository $tagRepository, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($tokenStorage);
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
                        [
                            UniqueNameForUser::REPOSITORY_OPTION => $this->tagRepository,
                            UniqueNameForUser::USER_OPTION => $this->user,
                            UniqueNameForUser::COLUMN_OPTION => 'name',
                            UniqueNameForUser::EXPECT_OPTION => $options['expect']
                        ]
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
