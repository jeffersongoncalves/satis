<?php

namespace App\Data;

use Illuminate\Contracts\Support\Arrayable;

class Package implements Arrayable
{
    public function __construct(
        public protected(set) string $name,
        public protected(set) string $version = '*'
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
        ];
    }
}
