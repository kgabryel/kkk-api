<?php

namespace App\Controller;

use App\Dto\ApiKey;
use App\Dto\Settings;
use App\Factory\Entity\ApiKeyFactory;
use App\Form\ChangePasswordForm;
use App\Form\OzaKeyForm;
use App\Form\RegisterForm;
use App\Repository\ApiKeyRepository;
use App\Service\Auth\RegistrationService;
use App\Service\Entity\ApiKeyService;
use App\Service\Entity\IngredientService;
use App\Service\Entity\SettingsService;
use App\Service\OzaSuppliesService;
use App\Service\SerializeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends BaseController
{
    public function register(Request $request, RegistrationService $registration): Response
    {
        $form = $this->createForm(RegisterForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $registration->register($form->getData());

            return new Response(null, Response::HTTP_CREATED);
        }

        return $this->returnErrors($form);
    }

    public function getKeys(ApiKeyRepository $apiKeyRepository): Response
    {
        $serializer = SerializeService::getInstance(ApiKey::class);

        return new Response($serializer->serializeArray($apiKeyRepository->findForUser($this->getUser())));
    }

    public function generateKey(ApiKeyFactory $apiKeyFactory): Response
    {
        $apiKey = $apiKeyFactory->generate();
        if ($apiKey === null) {
            return new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $serializer = SerializeService::getInstance(ApiKey::class);

        return new Response($serializer->serialize($apiKey));
    }

    public function destroyKey(int $id, ApiKeyService $apiKeyService): Response
    {
        if (!$apiKeyService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $apiKeyService->remove();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function switchKey(int $id, ApiKeyService $apiKeyService): Response
    {
        if (!$apiKeyService->find($id)) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }
        $apiKeyService->switch();
        $serializer = SerializeService::getInstance(ApiKey::class);

        return new Response($serializer->serialize($apiKeyService->getKey()));
    }

    public function switchAutocomplete(SettingsService $settingsService): Response
    {
        $settingsService->switchAutocomplete();
        $serializer = SerializeService::getInstance(Settings::class);

        return new Response($serializer->serialize($settingsService->getSettings()));
    }

    public function getSettings(SettingsService $settingsService): Response
    {
        $serializer = SerializeService::getInstance(Settings::class);

        return new Response($serializer->serialize($settingsService->getSettings()));
    }

    public function changeOzaKey(
        Request $request,
        SettingsService $settingsService,
        IngredientService $ingredientService,
        OzaSuppliesService $ozaSuppliesService
    ): Response {
        $form = $this->createForm(OzaKeyForm::class, null, [
            self::METHOD => Request::METHOD_PATCH
        ]);
        if (!$settingsService->changeOzaKey($form, $request, $ingredientService, $ozaSuppliesService)) {
            return $this->returnErrors($form);
        }
        $serializer = SerializeService::getInstance(Settings::class);

        return new Response($serializer->serialize($settingsService->getSettings()));
    }

    public function changePassword(
        SettingsService $settingsService,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        if ($this->getUser()->getFbId() !== null) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }

        $form = $this->createForm(ChangePasswordForm::class);
        if (!$settingsService->changePassword($form, $request, $passwordEncoder)) {
            return $this->returnErrors($form);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
