<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers;

use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

interface FieldRenderer
{
    public function render(FieldInterface $field): string;
}
