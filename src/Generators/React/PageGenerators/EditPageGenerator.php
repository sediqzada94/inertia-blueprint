<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\PageGenerators;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

class EditPageGenerator extends BasePageGenerator
{
    protected string $pageName = 'Edit';

    protected function getReplacements(Collection $fields): array
    {
        return [
            '{{ model }}' => $this->config->model,
            '{{ modelCamel }}' => $this->getModelCamel(),
            '{{ fields }}' => $this->getFields($fields),
            '{{ formFieldsDefault }}' => $this->getFormFieldsDefault($fields),
            '{{ routeUpdate }}' => $this->resolveRoute($this->config->routes['update'] ?? null, 'update'),
            '{{ routeIndex }}' => $this->resolveRoute($this->config->routes['index'] ?? null, 'index'),
            '{{ formInputs }}' => $this->getFormInputs($fields),
            '{{ selectTypes }}' => $this->getSelectTypes($fields),
            '{{ inputs }}' => $this->getInputs($fields),
            '{{ propsTypes }}' => $this->getPropsTypes($fields),
            '{{ selectFieldStaticOptions }}' => $this->getStaticOptions($fields),
        ];
    }

    private function getFields(Collection $fields): string
    {
        return $fields
            ->map(function (FieldInterface $field): string {
                $fieldConfig = $field->getConfig();
                $defaultValue = $fieldConfig->inputType === 'file' ? 'string | File' : $this->getTypeScriptType($fieldConfig->type);
                $fieldName = $fieldConfig->fieldName ?? $fieldConfig->name;

                return "    {$fieldName}: {$defaultValue}";
            })
            ->implode(PHP_EOL);
    }

    private function getFormFieldsDefault(Collection $fields): string
    {
        $modelVar = $this->getModelCamel();

        return $fields
            ->map(function (FieldInterface $field) use ($modelVar): string {
                $fieldConfig = $field->getConfig();
                $fieldName = $fieldConfig->fieldName ?? $fieldConfig->name;

                return "    {$fieldName}: {$modelVar}.{$fieldName},";
            })
            ->implode(PHP_EOL);
    }

    private function getInputs(Collection $fields): string
    {
        $selectInputs = $fields
            ->filter(fn (FieldInterface $field): bool => $field->getConfig()->inputType === 'select' && ! is_array($field->getConfig()->options))
            ->map(fn (FieldInterface $field) => Str::of($field->getConfig()->name)->plural())
            ->implode(', ');

        $modelVar = $this->getModelCamel();

        return $selectInputs ? "{$modelVar}, {$selectInputs}" : $modelVar;
    }
}
