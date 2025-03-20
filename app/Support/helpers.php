<?php

namespace App\Support;

use Illuminate\Support\Str;

if (! function_exists('App\Support\tenant')) {
    /**
     * @template TValue
     *
     * @param  class-string<TValue>  $class
     * @return TValue|mixed
     */
    function tenant(string $class, ?string $attribute = null): mixed
    {
        return once(function () use ($class, $attribute) {
            $tenant = \Filament\Facades\Filament::getTenant();

            if (! $tenant instanceof $class) {
                return null;
            }

            if (is_null($attribute)) {
                return $tenant;
            }

            return $tenant?->getAttribute($attribute) ?? null;
        });
    }
}

if (! function_exists('App\Support\html')) {
    function html(?string $html = null): ?\Illuminate\Support\HtmlString
    {
        if (! $html) {
            return null;
        }

        return new \Illuminate\Support\HtmlString($html);
    }
}

if (! function_exists('blade')) {
    function blade(?string $string = null, array $data = [], bool $deleteCachedView = false): ?string
    {
        if (! $string) {
            return null;
        }

        return \Illuminate\Support\Facades\Blade::render($string, $data, $deleteCachedView);
    }
}

if (! function_exists('App\Support\enum_equals')) {
    function enum_equals(\BackedEnum|string|int|null $value, \BackedEnum|array $enum): bool
    {
        if (is_array($enum)) {
            return array_reduce($enum, fn (bool $carry, \BackedEnum $enum) => $carry || enum_equals($enum, $value), false);
        }

        if (! $value instanceof \BackedEnum) {
            return $enum::tryFrom($value) === $enum;
        }

        return $enum === $value;
    }
}

if (! function_exists('App\Support\package_name')) {
    function package_name(?string $value = null): ?array
    {
        if (! $value) {
            return [null, null, null];
        }

        preg_match('/^([^\/]+)\/([^~@+.\s]+)/', $value, $matches);

        return $matches;
    }
}

if (! function_exists('App\Support\email_slug')) {
    function email_slug(?string $email = null): ?string
    {
        if (! $email) {
            return null;
        }

        return Str::slug(
            title: $email,
            separator: '_',
            dictionary: ['@' => '_at_', '.' => '_dot_']
        );
    }
}

if (! function_exists('App\Support\array_merge_recursive_unique')) {
    function array_merge_recursive_unique(array $array1, array $array2): array
    {
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = array_merge_recursive_unique($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }

        return $array1;
    }
}
