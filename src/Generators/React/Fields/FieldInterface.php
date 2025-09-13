<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\Fields;

use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;

interface FieldInterface
{
    public function getName(): string;

    public function getFieldName(): string;

    public function getInputType(): string;

    public function getLabel(): string;

    public function getDefaultValueDeclaration(): string;

    public function render(): string;

    public function getTypeDefinition(): string;

    public function getInputDeclaration(): string;

    public function getPropTypeDeclaration(): string;

    public function getConfig(): FieldConfigDTO;
}
