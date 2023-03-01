<?php

namespace App\Support\Traits;

use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

trait HasRouteRegister
{
    protected static string $prefix;

    protected static array $actionList;

    protected static array $actionNames;

    protected static function getRoutePrefix($action): string
    {
        return static::getRouteName().'.'.$action;
    }

    public static function getRouteName(): string
    {
        return static::$prefix
            ?? Str::of(class_basename(static::class))
                ->kebab()
                ->replace('-controller', '');
    }

    public static function getRouteActionList(): array
    {
        $methods = static::getResourceDefaults();

        if (isset(static::$actionList) && ! empty(static::$actionList)) {
            $methods = array_intersect($methods, static::$actionList);
        }

        return $methods;
    }

    public static function getRouteActionNames(): array
    {
        return array_map(
            fn ($action) => static::getRoutePrefix($action),
            static::$actionNames ?? []
        );
    }

    /**
     * Register the routes for the controller.
     */
    public static function routes(): void
    {
        $options = [
            'only' => static::getRouteActionList(),
            'names' => static::getRouteActionNames(),
        ];

        $resource = Route::resource(static::getRouteName(), static::class);

        foreach (array_filter($options) as $option => $args) {
            $resource->{$option}($args);
        }

        static::additionalRoutes();
    }

    public static function getResourceDefaults()
    {
        $resourceDefaults = [];

        try {
            $resourceDefaults = (new ReflectionClass(ResourceRegistrar::class))
                ->getProperty('resourceDefaults')
                ->getDefaultValue();
        } catch (ReflectionException $e) {
        } finally {
            return $resourceDefaults;
        }
    }

    public static function additionalRoutes(): void
    {
    }
}
