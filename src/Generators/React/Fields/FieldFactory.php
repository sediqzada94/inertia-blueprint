<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\Fields;

use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\CheckboxRenderer;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\FileRenderer;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\SelectRenderer;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\TextareaRenderer;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\TextRenderer;

class FieldFactory
{
    public static function create(FieldConfigDTO $config, ?string $context = null): FieldInterface
    {
        $renderer = match ($config->inputType) {
            'checkbox' => new CheckboxRenderer,
            'select' => (new SelectRenderer)->setContext($context ?? 'create'),
            'file' => (new FileRenderer)->setContext($context ?? 'create'),
            'textarea' => new TextareaRenderer,
            default => new TextRenderer,
        };

        return new Field($config, $renderer);
    }
}
