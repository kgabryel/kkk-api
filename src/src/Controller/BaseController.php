<?php

namespace App\Controller;

use App\Entity\User;
use Kgabryel\ErrorConverter\FormErrorConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/** @method User getUser() */
abstract class BaseController extends AbstractController
{
    public const METHOD = 'method';
    public const EXPECT = 'expect';

    protected function returnErrors(FormInterface $form): JsonResponse
    {
        $formError = new FormErrorConverter($form);
        $formError->parse();

        return new JsonResponse($formError->getErrors(), Response::HTTP_BAD_REQUEST);
    }
}
