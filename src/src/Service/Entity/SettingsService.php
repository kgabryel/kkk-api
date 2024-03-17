<?php

namespace App\Service\Entity;

use App\Entity\Settings;
use App\Entity\User;
use App\Model\OzaKey;
use App\Service\OzaSuppliesService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SettingsService extends EntityService
{
    private Settings $settings;

    public function __construct(EntityManagerInterface $entityManager, UserService $userService)
    {
        parent::__construct($entityManager, $userService);
        $this->settings = $this->user->getSettings();
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public static function get(User $user): Settings
    {
        $settings = new Settings();
        $settings->setAutocomplete(true);
        $settings->setOzaKey(null);
        $settings->setUser($user);

        return $settings;
    }

    public function find(int $id): bool
    {
        return true;
    }

    public function switchAutocomplete(): self
    {
        $this->settings->setAutocomplete(!$this->settings->getAutocomplete());
        $this->saveEntity($this->settings);

        return $this;
    }

    public function changeOzaKey(
        FormInterface $form,
        Request $request,
        IngredientService $ingredientService,
        OzaSuppliesService $ozaSuppliesService
    ): bool {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }
        /** @var OzaKey $data */
        $data = $form->getData();
        $key = $data->getKey();
        if ($key === null) {
            $ingredientService->clearOzaKeys();
        }
        $this->settings->setOzaKey($key);
        $this->saveEntity($this->settings);
        $ozaSuppliesService->setKey();
        if ($this->settings->getOzaKey() !== null) {
            $ingredient = $ingredientService->getFirstIngredientWithOza();
            if ($ingredient !== null) {
                if ($ozaSuppliesService->downloadSupplies()) {
                    $supplies = array_map(static fn($supply): int => $supply->id, $ozaSuppliesService->getSupplies());
                    if (!in_array($ingredient->getOzaId(), $supplies, true)) {
                        $ingredientService->clearOzaKeys();
                    }
                } else {
                    $ingredientService->clearOzaKeys();
                }
            }
        }

        return true;
    }

    public function changePassword(
        FormInterface $form,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ): bool {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }
        $password = $form->getData()->getNewPassword();

        $this->user->setPassword($passwordEncoder->encodePassword($this->user, $password));
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();

        return true;
    }
}
