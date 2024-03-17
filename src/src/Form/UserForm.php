<?php

namespace App\Form;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\Form\AbstractType;

abstract class UserForm extends AbstractType
{
    protected User $user;

    public function __construct(UserService $userService)
    {
        $this->user = $userService->getUser();
    }
}
