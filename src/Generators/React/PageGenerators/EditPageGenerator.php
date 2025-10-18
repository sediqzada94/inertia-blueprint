<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\PageGenerators;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sediqzada\InertiaBlueprint\Contracts\PageGeneratorInterface;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldFactory;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;

class EditPageGenerator implements PageGeneratorInterface
{
    private string $pageName = 'Edit';

    public function __construct(
        private readonly PageConfigDTO $config,
        private readonly PageGeneratorService $pageGenerator
    ) {}

    public function generate(): void
    {
        $outputPath = $this->pageGenerator->getOutputPath($this->config->model, $this->pageName);
        $stub = $this->replacedStubPlaceholders();
        $this->pageGenerator->writeToFile($outputPath, $stub);
    }

    private function replacedStubPlaceholders(): string
    {
        $fields = $this->createFields();

        $replacements = [
            '{{ model }}' => $this->config->model,
            '{{ modelCamel }}' => Str::of($this->config->model)->camel(),
            '{{ fields }}' => $this->getFields($fields),
            '{{ formFieldsDefault }}' => $this->getFormFieldsDefault($fields),
            '{{ routeUpdate }}' => $this->pageGenerator->resolveRoute(
                $this->config->routes['update'] ?? null,
                $this->config->model,
                'update'
            ),
            '{{ routeIndex }}' => $this->pageGenerator->resolveRoute(
                $this->config->routes['index'] ?? null,
                $this->config->model,
                'index'
            ),
            '{{ formInputs }}' => $this->getFormInputs($fields),
            '{{ selectTypes }}' => $this->getSelectTypes($fields),
            '{{ inputs }}' => $this->getInputs($fields),
            '{{ propsTypes }}' => $this->getPropsTypes($fields),
            '{{ selectFieldStaticOptions }}' => $this->getStaticOptions($fields),
        ];

        return $this->pageGenerator->replacePlaceholders(
            $replacements,
            $this->pageGenerator->readStub($this->pageName)
        );
    }

    /**
     * @return Collection<int, FieldInterface>
     */
    private function createFields(): Collection
    {
        return collect($this->config->fields)
            ->map(fn (FieldConfigDTO $field): FieldInterface => FieldFactory::create($field, 'edit'));
    }

    /**
     * @param  FieldCollection  $fields
     */
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

    /**
     * @param  FieldCollection  $fields
     */
    private function getFormFieldsDefault(Collection $fields): string
    {
        $modelVar = Str::of($this->config->model)->camel();

        return $fields
            ->map(function (FieldInterface $field) use ($modelVar): string {
                $fieldConfig = $field->getConfig();
                $fieldName = $fieldConfig->fieldName ?? $fieldConfig->name;

                return "    {$fieldName}: {$modelVar}.{$fieldName},";
            })
            ->implode(PHP_EOL);
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getFormInputs(Collection $fields): string
    {
        $gridFields = $fields->filter(fn (FieldInterface $field): bool => $field->getInputType() !== 'textarea');
        $textareaFields = $fields->filter(fn (FieldInterface $field): bool => $field->getInputType() === 'textarea');

        $gridFieldsHtml = $gridFields
            ->map(fn (FieldInterface $field): string => $this->indentContent($field->render(), 12))
            ->implode(PHP_EOL);

        $textareaFieldsHtml = $textareaFields
            ->map(fn (FieldInterface $field): string => $this->indentContent($field->render(), 10))
            ->implode(PHP_EOL);

        return $gridFieldsHtml.($textareaFieldsHtml ? PHP_EOL.'          </div>'.PHP_EOL.$textareaFieldsHtml : PHP_EOL.'          </div>');
    }

    private function indentContent(string $content, int $spaces): string
    {
        $indent = Str::of(' ')->repeat($spaces);
        $lines = Str::of($content)->trim()->explode("\n");

        return $indent.$lines->implode("\n".$indent);
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getSelectTypes(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field): string => $field->getTypeDefinition())
            ->filter()
            ->implode(PHP_EOL.PHP_EOL);
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getInputs(Collection $fields): string
    {
        $selectInputs = $fields
            ->filter(fn (FieldInterface $field): bool => $field->getConfig()->inputType === 'select' && !is_array($field->getConfig()->options))
            ->map(fn (FieldInterface $field) => Str::of($field->getConfig()->name)->plural())
            ->implode(', ');

        $modelVar = Str::of($this->config->model)->camel();

        return $selectInputs ? "{$modelVar}, {$selectInputs}" : $modelVar;
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getPropsTypes(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field): string => $field->getPropTypeDeclaration())
            ->filter()
            ->implode(PHP_EOL);
    }

    private function getTypeScriptType(string $type): string
    {
        return match ($type) {
            'string', 'text', 'email', 'password' => 'string',
            'integer', 'number' => 'number',
            'boolean' => 'boolean',
            'datetime', 'date' => 'string',
            default => 'string'
        };
    }

    private function getStaticOptions(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field): string => $field->getFieldOption())
            ->filter()
            ->implode(PHP_EOL);
    }
}
