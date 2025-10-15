<?php

namespace App\Controller;

use App\Entity\ApiKey as ApiKeyEntity;
use App\Factory\Entity\ApiKeyFactory;
use App\Repository\ApiKeyRepository;
use App\Response\ApiKeyListResponse;
use App\Response\ApiKeyResponse;
use App\Response\SettingsResponse;
use App\Service\Auth\RegistrationService;
use App\Service\Entity\ApiKeyService;
use App\Service\Entity\IngredientService;
use App\Service\Entity\SettingsService;
use App\Service\OzaSuppliesService;
use App\Validation\ChangePasswordValidation;
use App\Validation\OzaKeyValidation;
use App\Validation\RegisterValidation;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    public function changeOzaKey(
        SettingsService $settingsService,
        IngredientService $ingredientService,
        OzaSuppliesService $ozaSuppliesService,
        OzaKeyValidation $ozaKeyValidation,
    ): Response {
        if (!$settingsService->changeOzaKey($ozaKeyValidation, $ingredientService, $ozaSuppliesService)) {
            return $this->getBadRequestResponse();
        }

        return new SettingsResponse($this->dtoFactoryDispatcher, $settingsService->getSettings(), Response::HTTP_OK);
    }

    public function changePassword(
        SettingsService $settingsService,
        ChangePasswordValidation $changePasswordValidation,
    ): Response {
        if ($this->getUser()->getFbId() !== null) {
            return $this->getForbiddenResponse();
        }

        if (!$settingsService->changePassword($changePasswordValidation)) {
            return $this->getBadRequestResponse();
        }

        return $this->getNoContentResponse();
    }

    public function destroyKey(int $id, ApiKeyService $apiKeyService): Response
    {
        $apiKey = $apiKeyService->find($id);
        if (!$apiKey instanceof ApiKeyEntity) {
            return $this->getNotFoundResponse();
        }

        $apiKeyService->remove($apiKey);

        return $this->getNoContentResponse();
    }

    public function generateKey(ApiKeyFactory $apiKeyFactory): Response
    {
        $apiKey = $apiKeyFactory->generate();
        if (!($apiKey instanceof ApiKeyEntity)) {
            return $this->getInternalServerErrorResponse();
        }

        return new ApiKeyResponse($this->dtoFactoryDispatcher, $apiKey, Response::HTTP_CREATED);
    }

    public function getKeys(ApiKeyRepository $apiKeyRepository): Response
    {
        return new ApiKeyListResponse(
            $this->dtoFactoryDispatcher,
            ...$apiKeyRepository->findForUser($this->getUser()),
        );
    }

    public function getSettings(SettingsService $settingsService): SettingsResponse
    {
        return new SettingsResponse($this->dtoFactoryDispatcher, $settingsService->getSettings(), Response::HTTP_OK);
    }

    public function register(RegisterValidation $registerValidation, RegistrationService $registration): Response
    {
        if (!$registerValidation->validate()->passed()) {
            return $this->getBadRequestResponse();
        }

        $registration->register($registerValidation->getDto()->getUser());

        return $this->getCreatedResponse();
    }

    public function switchAutocomplete(SettingsService $settingsService): SettingsResponse
    {
        $settingsService->switchAutocomplete();

        return new SettingsResponse($this->dtoFactoryDispatcher, $settingsService->getSettings(), Response::HTTP_OK);
    }

    public function switchKey(int $id, ApiKeyService $apiKeyService): Response
    {
        $apiKey = $apiKeyService->find($id);
        if (!$apiKey instanceof ApiKeyEntity) {
            return $this->getNotFoundResponse();
        }

        $apiKeyService->switch($apiKey);

        return new ApiKeyResponse($this->dtoFactoryDispatcher, $apiKey, Response::HTTP_OK);
    }
}
