<?php

namespace App\Service\Entity;

use App\Entity\Ingredient;
use App\Entity\Settings;
use App\Service\OzaSuppliesService;
use App\Service\UserService;
use App\Validation\ChangePasswordValidation;
use App\Validation\OzaKeyValidation;
use Doctrine\ORM\EntityManagerInterface;

class SettingsService extends EntityService
{
    private Settings $settings;

    public function __construct(EntityManagerInterface $entityManager, UserService $userService)
    {
        parent::__construct($entityManager, $userService);
        $this->settings = $this->user->getSettings();
    }

    public function changeOzaKey(
        OzaKeyValidation $ozaKeyValidation,
        IngredientService $ingredientService,
        OzaSuppliesService $ozaSuppliesService,
    ): bool {
        if (!$ozaKeyValidation->validate()->passed()) {
            return false;
        }

        $data = $ozaKeyValidation->getDto();
        $key = $data->getKey();
        if ($key === null) {
            $ingredientService->clearOzaKeys();
        }
        $this->settings->setOzaKey($key);
        $this->saveEntity($this->settings);
        if ($key === null) {
            return true;
        }

        $ozaSuppliesService->setKey();

        /*
         * Pobiera pierwszy składnik z przypisanym ID OZA
         * Służy do sprawdzenia czy istnieje powiązanie z OZA, a w następnym kroku czy klucz OZA został podmieniony
         * na klucz z tego samego konta
         */
        $ingredient = $ingredientService->getFirstIngredientWithOza();
        if (!$ingredient instanceof Ingredient) {
            return true;
        }

        if (!$ozaSuppliesService->downloadSupplies()) {
            $ingredientService->clearOzaKeys();

            return true;
        }

        // Lista ID zapasów po pobraniu nowego klucza
        $supplies = array_map(static fn ($supply): int => $supply->id, $ozaSuppliesService->getSupplies());

        /*
         * Sprawdza, czy OZA ID przypisane do składnika nadal istnieje w liście dostępnych zasobów (po zmianie klucza).
         * Jeśli nie – prawdopodobnie użyto klucza z innego konta.
         * Wtedy czyścimy wszystkie przypisania składników do zapasów OZA.
         */
        if (!in_array($ingredient->getOzaId(), $supplies, true)) {
            $ingredientService->clearOzaKeys();
        }

        return true;
    }

    public function changePassword(ChangePasswordValidation $changePasswordValidation): bool
    {
        if (!$changePasswordValidation->validate()->passed()) {
            return false;
        }

        $this->user->setPassword($changePasswordValidation->getDto()->getPassword());
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();

        return true;
    }

    public function find(int $id): ?object
    {
        return null;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function switchAutocomplete(): self
    {
        $this->settings->setAutocomplete(!$this->settings->getAutocomplete());
        $this->saveEntity($this->settings);

        return $this;
    }
}
