<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\PageGenerators;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

class CreatePageGenerator extends BasePageGenerator
{
    protected string $pageName = 'Create';

    protected function getReplacements(Collection $fields): array
    {
        return [
            '{{ formFieldsDefault }}' => $this->getFormFieldsDefault($fields),
            '{{ routeStore }}' => $this->resolveRoute($this->config->routes['store'] ?? null, 'store'),
            '{{ routeIndex }}' => $this->resolveRoute($this->config->routes['index'] ?? null, 'index'),
            '{{ model }}' => $this->config->model,
            '{{ formInputs }}' => $this->getFormInputs($fields),
            '{{ selectTypes }}' => $this->getSelectTypes($fields),
            '{{ selectInputs }}' => $this->getSelectInputs($fields),
            '{{ propsTypes }}' => $this->getPropsTypes($fields),
            '{{ selectFieldStaticOptions }}' => $this->getStaticOptions($fields),
        ];
    }

    private function getFormFieldsDefault(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field) => Str::of($field->getDefaultValueDeclaration())->prepend('    '))
            ->implode(PHP_EOL);
    }

    private function getSelectInputs(Collection $fields): string
    {
        $selectInputs = $fields
            ->map(fn (FieldInterface $field): string => $field->getInputDeclaration())
            ->filter()
            ->implode(', ');

        return $selectInputs ?: '';
    }
}
