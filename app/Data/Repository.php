<?php

namespace App\Data;

use Illuminate\Contracts\Support\Arrayable;

readonly class Repository implements Arrayable
{
    public function __construct(
        public string $name,
        public string $type,
        public string $url,
        public array  $options = []
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'url' => $this->url,
            'options' => $this->options,
        ];
    }
}
