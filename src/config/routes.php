<?php

use App\Config\PhotoType;
use App\Controller\AuthController;
use App\Controller\IngredientsController;
use App\Controller\OzaSuppliesController;
use App\Controller\RecipesController;
use App\Controller\PhotosController;
use App\Controller\ResetPasswordController;
use App\Controller\SeasonsController;
use App\Controller\TagsController;
use App\Controller\TimersController;
use App\Controller\UserController;
use Kgabryel\Routing\Group;
use Kgabryel\Routing\Info;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Route;

return static function (RoutingConfigurator $routes) {
    /** Authentication routes */
    $routes->add('api_login_check', '/api/login_check');
    $routes->add('gesdinet_jwt_refresh_token', ' /api/refresh_token')
        ->controller('gesdinet.jwtrefreshtoken::refresh');
    $routes->add('register', ' /api/register')
        ->controller(sprintf('%s::%s', UserController::class, 'register'))
        ->methods([Request::METHOD_POST]);
    $authGroup = new Group($routes, 'auth', 'api/facebook');
    $authGroup->setController(AuthController::class)
        ->add('fbRedirect', new Route('/redirect'), new Info([Request::METHOD_GET], 'getRedirectUrl'))
        ->add('fbLogin', new Route('/login'), new Info([Request::METHOD_POST], 'login'));
    /** Ingredients routes */
    $ingredientsGroup = new Group($routes, 'ingredients', '/api/ingredients');
    $ingredientsGroup->setController(IngredientsController::class)
        ->addIndex()
        ->addStore()
        ->addModify()
        ->addDestroy()
        ->add(
            'getOzaSupplies',
            new Route('/oza-supplies'),
            new Info([Request::METHOD_GET], 'geOzaSupplies')
        );
    /** Tags routes */
    $tagsGroup = new Group($routes, 'tags', '/api/tags');
    $tagsGroup->setController(TagsController::class)
        ->addIndex()
        ->addStore()
        ->addUpdate()
        ->addDestroy();
    /** Recipe routes */
    $recipesGroup = new Group($routes, 'recipes', '/api/recipes');
    $recipesGroup->setController(RecipesController::class)
        ->addIndex()
        ->addStore()
        ->addUpdate()
        ->addDestroy()
        ->addModify();
    /** Season routes */
    $seasonsGroup = new Group($routes, 'seasons', '/api/seasons');
    $seasonsGroup->setController(SeasonsController::class)
        ->addIndex()
        ->addStore()
        ->addDestroy()
        ->addModify();
    /** Oza supplies routes */
    $ozaSuppliesGroup = new Group($routes, 'ozaSupplies', '/api/oza/supplies');
    $ozaSuppliesGroup->setController(OzaSuppliesController::class)
        ->addDestroy()
        ->addModify();
    /** settings routes */
    $settingsGroup = new Group($routes, 'settings', '/api/settings');
    $settingsGroup->setController(UserController::class)
        ->add(
            'getSettings',
            new Route('/'),
            new Info([Request::METHOD_GET], 'getSettings')
        )
        ->add(
            'switchAutocomplete',
            new Route('/switch-autocomplete'),
            new Info([Request::METHOD_PATCH], 'switchAutocomplete')
        )
        ->add(
            'changeOzaKey',
            new Route('/change-oza-key'),
            new Info([Request::METHOD_PATCH], 'changeOzaKey')
        )
        ->add(
            'changePassword',
            new Route('/change-password'),
            new Info([Request::METHOD_POST], 'changePassword')
        );
    /** api keys routes */
    $apiKeyGroup = new Group($routes, 'apiKey', '/api/api-keys');
    $apiKeyGroup->setController(UserController::class)
        ->add(
            'getKeys',
            new Route('/'),
            new Info([Request::METHOD_GET], 'getKeys')
        )
        ->add(
            'generateKey',
            new Route('/'),
            new Info([Request::METHOD_POST], 'generateKey')
        )
        ->add(
            'deleteKey',
            new Route('/{id}'),
            new Info([Request::METHOD_DELETE], 'destroyKey'),
            ['id' => '\d+']
        )
        ->add(
            'switchKey',
            new Route('/{id}'),
            new Info([Request::METHOD_PATCH], 'switchKey'),
            ['id' => '\d+']
        );
    /** api public recipes routes */
    $publicRecipesGroup = new Group($routes, 'publicRecipes', 'api/public/recipes');
    $publicRecipesGroup->setController(RecipesController::class)
        ->add(
            'publicRecipes',
            new Route('/{id}'),
            new Info([Request::METHOD_GET], 'public')
        );
    /** Timers routes */
    $timersGroup = new Group($routes, 'timers', '/api/timers');
    $timersGroup->setController(TimersController::class)
        ->addIndex()
        ->addStore()
        ->addUpdate()
        ->addDestroy();
    /** reset password routes */
    $resetPasswordGroup = new Group($routes, 'resetPassword', '');
    $resetPasswordGroup->setController(ResetPasswordController::class)
        ->add(
            'sendEmail',
            new Route('/api/reset-password'),
            new Info([Request::METHOD_POST], 'sendEmail')
        )
        ->add(
            'changePassword',
            new Route('/api/change-password/{token}'),
            new Info([Request::METHOD_POST], 'changePassword')
        )
        ->add(
            'checkToken',
            new Route('/api/check-token/{token}'),
            new Info([Request::METHOD_GET], 'checkToken')
        );
    $photosGroup = new Group($routes, 'photos', '/api/recipes');
    $photosGroup->setController(PhotosController::class)
        ->add(
            'store',
            new Route('/{id}/photos'),
            new Info([Request::METHOD_POST], 'store'),
            ['id' => '\d+']
        )
        ->add(
            'destroy',
            new Route('/{recipeId}/photos/{photoId}'),
            new Info([Request::METHOD_DELETE], 'destroy'),
            ['recipeId' => '\d+', 'photoId' => '\d+']
        )
        ->add(
            'show',
            new Route('/{recipeId}/photos/{photoId}/{type}'),
            new Info([Request::METHOD_GET], 'show'),
            [
                'type' => sprintf('(%s)', implode('|', array_column(PhotoType::cases(), 'value'))),
                'recipeId' => '\d+',
                'photoId' => '\d+'
            ]
        )
        ->add(
            'reorderPhotos',
            new Route('/{id}/photos'),
            new Info([Request::METHOD_PATCH], 'reorderPhotos'),
            ['id' => '\d+']
        );
    return $routes;
};
