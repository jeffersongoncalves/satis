<?php

namespace App\Support;

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
