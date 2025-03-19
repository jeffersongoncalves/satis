<?php

namespace App\Data;

use Illuminate\Contracts\Support\Arrayable;

class Repository implements Arrayable
{
    public function __construct(
        public protected(set) string $type,
        public protected(set) string $url,
        public protected(set) array $options = []
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'url' => $this->url,
            'options' => $this->options,
        ];
    }
}
