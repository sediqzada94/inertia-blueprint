<?php

namespace Sediqzada\InertiaBlueprint\Generators\React;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Sediqzada\InertiaBlueprint\Contracts\PageGeneratorInterface;

class ReactPageGenerator
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config) {}

    public function generate(string $page): void
    {
        $namespace = "Sediqzada\\InertiaBlueprint\Generators\\React\\PageGenerators\\";
        $class = $namespace.Str::of($page)->studly().'PageGenerator';

        if (! class_exists($class)) {
            throw new InvalidArgumentException("Page generator class [{$class}] does not exist.");
        }

        /** @var PageGeneratorInterface $generator */
        $generator = new $class($this->config);

        $generator->generate();
    }
}
