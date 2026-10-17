<?php

namespace App\Controller;

use App\Entity\User;
use App\Factory\DtoFactoryDispatcher;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends AbstractController
{
    protected DtoFactoryDispatcher $dtoFactoryDispatcher;
    protected UserRepository $userRepository;

    public function __construct(DtoFactoryDispatcher $dtoFactoryDispatcher, UserRepository $userRepository)
    {
        $this->dtoFactoryDispatcher = $dtoFactoryDispatcher;
        $this->userRepository = $userRepository;
    }

    protected function getBadRequestResponse(): Response
    {
        return $this->getResponse(Response::HTTP_BAD_REQUEST);
    }

    protected function getCreatedResponse(): Response
    {
        return $this->getResponse(Response::HTTP_CREATED);
    }

    protected function getForbiddenResponse(): Response
    {
        return $this->getResponse(Response::HTTP_FORBIDDEN);
    }

    protected function getInternalServerErrorResponse(): Response
    {
        return $this->getResponse(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function getNoContentResponse(): Response
    {
        return $this->getResponse(Response::HTTP_NO_CONTENT);
    }

    protected function getNotFoundResponse(): Response
    {
        return $this->getResponse(Response::HTTP_NOT_FOUND);
    }

    protected function getUnauthorizedResponse(): Response
    {
        return $this->getResponse(Response::HTTP_UNAUTHORIZED);
    }

    protected function getUser(): User
    {
        $user = parent::getUser();
        $userEntity = $this->userRepository->find($user?->getId() ?? 0);

        return $userEntity ?? new User();
    }

    private function getResponse(int $status): Response
    {
        return new Response(status: $status);
    }
}
