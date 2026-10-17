<?php

namespace App\Tests\Helper;

use App\Entity\ApiKey;
use App\Entity\Ingredient;
use App\Entity\Photo;
use App\Entity\Recipe;
use App\Entity\RecipePosition;
use App\Entity\RecipePositionGroup;
use App\Entity\Season;
use App\Entity\Settings;
use App\Entity\Tag;
use App\Entity\Timer;
use App\Entity\User;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\IngredientFactory;
use App\Tests\Factory\RecipeFactory;
use App\Tests\Factory\RecipePositionFactory;
use App\Tests\Factory\RecipePositionGroupFactory;
use App\Tests\Factory\SeasonFactory;
use App\Tests\Factory\SettingsFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TimerFactory;
use App\Tests\Factory\UserFactory;
use ReflectionClass;
use stdClass;

class EntityFactory
{
    public const string USER_EMAIL = 'email@example.com';
    public const string USER_EMAIL_2 = 'email2@example.com';
    public const string USER_EMAIL_3 = 'email3@example.com';

    public static function createApiKey(string $userEmail, array $data = []): ApiKey
    {
        $data['user'] = self::createUser($userEmail);

        return ApiKeyFactory::createOne($data)->_real();
    }

    public static function createIngredient(string $userEmail, array $data = []): Ingredient
    {
        $data['user'] = self::createUser($userEmail);

        return IngredientFactory::createOne($data)->_real();
    }

    public static function createRecipe(string $userEmail, array $data = []): Recipe
    {
        $data['user'] = self::createUser($userEmail);

        return RecipeFactory::createOne($data)->_real();
    }

    public static function createRecipePosition(RecipePositionGroup $group, array $data = []): RecipePosition
    {
        $data['recipePositionGroup'] = $group;

        return RecipePositionFactory::createOne($data)->_real();
    }

    public static function createRecipePositionsGroup(Recipe $recipe, array $data = []): RecipePositionGroup
    {
        $data['recipe'] = $recipe;

        return RecipePositionGroupFactory::createOne($data)->_real();
    }

    public static function createSeason(string $userEmail, array $data = []): Season
    {
        $data['user'] = self::createUser($userEmail);

        return SeasonFactory::createOne($data)->_real();
    }

    public static function createSettings(string $userEmail, array $data = []): Settings
    {
        $data['user'] = self::createUser($userEmail);

        return SettingsFactory::createOne($data)->_real();
    }

    public static function createTag(string $userEmail, array $data = []): Tag
    {
        $data['user'] = self::createUser($userEmail);

        return TagFactory::createOne($data)->_real();
    }

    public static function createTimer(string $userEmail, array $data = []): Timer
    {
        $data['user'] = self::createUser($userEmail);

        return TimerFactory::createOne($data)->_real();
    }

    public static function createUser(string $email, array $data = []): User
    {
        $data['email'] = $email;

        return UserFactory::findOrCreate($data)->_real();
    }

    public static function getSimpleApiKey(int $id = 1): ApiKey
    {
        $apiKey = new ApiKey();
        self::overridePrivateProperty($apiKey, 'id', $id);
        $apiKey->setKey(str_repeat('a', 128))->activate();

        return $apiKey;
    }

    public static function getSimpleIngredient(int $id = 1): Ingredient
    {
        $ingredient = new Ingredient();
        self::overridePrivateProperty($ingredient, 'id', $id);
        $ingredient->setName('')->setAvailable(false)->setOzaId(null);

        return $ingredient;
    }

    public static function getSimpleOzaSupply(int $id = 1): object
    {
        $ozaSupply = new stdClass();
        $ozaSupply->id = $id;
        $ozaSupply->amount = 0;
        $ozaSupply->unit = new stdClass();
        $ozaSupply->unit->shortcut = '';
        $ozaSupply->group = new stdClass();
        $ozaSupply->group->name = '';

        return $ozaSupply;
    }

    public static function getSimplePhoto(int $id = 1): Photo
    {
        $photo = new Photo();
        self::overridePrivateProperty($photo, 'id', $id);
        $photo->setWidth(1)->setHeight(1)->setType('')->setPhotoOrder(999);

        return $photo;
    }

    public static function getSimpleRecipe(int $id = 1): Recipe
    {
        $recipe = new Recipe();
        self::overridePrivateProperty($recipe, 'id', $id);
        $recipe->setName('')
            ->setFavourite(false)
            ->setToDo(false)
            ->setDescription(null)
            ->setUrl(null)
            ->setPortions(1)
            ->setPublic(false)
            ->setPublicId('');

        return $recipe;
    }

    public static function getSimpleRecipePosition(int $id = 1): RecipePosition
    {
        $recipePosition = new RecipePosition();
        self::overridePrivateProperty($recipePosition, 'id', $id);

        return $recipePosition;
    }

    public static function getSimpleRecipePositionGroup(int $id = 1): RecipePositionGroup
    {
        $recipePositionGroup = new RecipePositionGroup();
        self::overridePrivateProperty($recipePositionGroup, 'id', $id);
        $recipePositionGroup->setName('');

        return $recipePositionGroup;
    }

    public static function getSimpleSeason(int $id = 1): Season
    {
        $season = new Season();
        self::overridePrivateProperty($season, 'id', $id);
        $season->setIngredient(self::getSimpleIngredient())->setStart(1)->setStop(2);

        return $season;
    }

    public static function getSimpleSettings(int $id = 1, ?User $user = null): Settings
    {
        $settings = new Settings();
        self::overridePrivateProperty($settings, 'id', $id);
        $settings->setAutocomplete(false)
            ->setOzaKey(null)
            ->setUser($user ?? self::getSimpleUser());

        return $settings;
    }

    public static function getSimpleTag(int $id = 1): Tag
    {
        $tag = new Tag();
        self::overridePrivateProperty($tag, 'id', $id);
        $tag->setName('');

        return $tag;
    }

    public static function getSimpleTimer(int $id = 1): Timer
    {
        $timer = new Timer();
        self::overridePrivateProperty($timer, 'id', $id);
        $timer->setName('')->setRecipe(null)->setTime(1);

        return $timer;
    }

    public static function getSimpleUser(int $id = 1): User
    {
        $user = new User();
        self::overridePrivateProperty($user, 'id', $id);
        $user->setEmail('')
            ->setFbId(null);

        return $user;
    }

    private static function overridePrivateProperty(object $object, string $name, mixed $value): void
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
