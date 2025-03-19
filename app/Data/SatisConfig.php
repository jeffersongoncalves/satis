<?php

namespace App\Data;

use Stringable;

use function App\Support\array_merge_recursive_unique;

class SatisConfig implements Stringable
{
    public protected(set) array $config = [];

    protected function __construct(
        public protected(set) ?string $path = null
    ) {}

    public static function make(): static
    {
        return new static();
    }

    public static function load(string $path): static
    {
        $static = new static($path);
        $static->read();

        return $static;
    }

    protected function read(): void
    {
        $this->config = json_decode(file_get_contents($this->path), true);
    }

    public function merge(SatisConfig $config): static
    {
        $this->config = array_merge_recursive_unique(
            $this->config,
            $config->config
        );

        return $this;
    }

    public function homepage(string $url): static
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid Homepage '{$url}'");
        }

        $this->config['homepage'] = $url;

        return $this;
    }

    public function repository(Repository $repository): static
    {
        $this->config['repositories'][] = $repository->toArray();

        return $this;
    }

    public function require(Package $package): static
    {
        $this->config['require'][$package->name] = $package->version;

        return $this;
    }

    public function save(): void
    {
        if($this->path === null) {
            throw new \RuntimeException('You must specify a path to save the config. Use saveAs() instead.');
        }

        rescue(fn () => mkdir(dirname($this->path), 0755, true), report: false);
        rescue(fn () => file_put_contents($this->path, $this->toJson()), report: false);
    }

    public function saveAs(string $path): void
    {
        $this->path = $path;
        $this->save();
    }

    public function delete(): void
    {
        rescue(fn ()=> unlink($this->path), report: false);
    }

    public function toJson(): string
    {
        return json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
