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

class IndexPageGenerator implements PageGeneratorInterface
{
    private string $pageName = 'Index';

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
            '{{ modelPluralCamel }}' => Str::of($this->config->model)->camel()->plural(),
            '{{ modelLower }}' => Str::of($this->config->model)->lower(),
            '{{ fields }}' => $this->getFields($fields),
            '{{ routeCreate }}' => $this->pageGenerator->resolveRoute(
                $this->config->routes['create'] ?? null,
                $this->config->model,
                'create'
            ),
            '{{ routeEdit }}' => $this->pageGenerator->resolveRoute(
                $this->config->routes['edit'] ?? null,
                $this->config->model,
                'edit'
            ),
            '{{ routeShow }}' => $this->pageGenerator->resolveRoute(
                $this->config->routes['show'] ?? null,
                $this->config->model,
                'show'
            ),
            '{{ routeDestroy }}' => $this->pageGenerator->resolveRoute(
                $this->config->routes['destroy'] ?? null,
                $this->config->model,
                'destroy'
            ),
            '{{ routeIndex }}' => $this->pageGenerator->resolveRoute(
                $this->config->routes['index'] ?? null,
                $this->config->model,
                'index'
            ),
            '{{ tableHeaders }}' => $this->generateTableHeaders($fields),
            '{{ tableCells }}' => $this->generateTableCells($fields),
            '{{ selectTypes }}' => $this->getSelectTypes($fields),
            '{{ hasSearchableFields }}' => $this->hasSearchableFields() ? 'true' : 'false',
            '{{ searchPlaceholder }}' => $this->getSearchPlaceholder(),
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
            ->map(fn (FieldConfigDTO $field): \Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface => FieldFactory::create($field, 'index'));
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getFields(Collection $fields): string
    {
        return $fields
            ->map(function (FieldInterface $field): string {
                $fieldConfig = $field->getConfig();

                if ($fieldConfig->inputType === 'select') {
                    // Check if options is an array (static options) or string (relationship)
                    if (is_array($fieldConfig->options)) {
                        // For array options, use the actual field name with its type
                        $actualFieldName = $fieldConfig->fieldName ?? $fieldConfig->name;

                        return "    {$actualFieldName}: {$this->getTypeScriptType($fieldConfig->type)}";
                    } else {
                        // For relationship options, use the relationship object type
                        $relationshipName = $fieldConfig->name;
                        $typeName = Str::of($fieldConfig->name)->title();

                        return "    {$relationshipName}: {$typeName}";
                    }
                } else {
                    // For regular fields, use the actual field name
                    $actualFieldName = $fieldConfig->fieldName ?? $fieldConfig->name;

                    return "    {$actualFieldName}: {$this->getTypeScriptType($fieldConfig->type)}";
                }
            })
            ->implode(PHP_EOL);
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function generateTableHeaders(Collection $fields): string
    {
        return $fields
            ->map(function (FieldInterface $field) {
                $fieldConfig = $field->getConfig();
                $nameTitle = Str::of($fieldConfig->name)->replace('_', ' ')->title();

                return Str::of("<TableHead>{$nameTitle}</TableHead>")->prepend(Str::of(' ')->repeat(14));
            })
            ->implode(PHP_EOL);
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function generateTableCells(Collection $fields): string
    {
        return $fields
            ->map(function (FieldInterface $field) {
                $fieldConfig = $field->getConfig();

                if ($fieldConfig->inputType === 'select') {
                    // Check if options is an array (static options) or string (relationship)
                    if (is_array($fieldConfig->options)) {
                        // For array options, use the field value directly
                        $actualFieldName = $fieldConfig->fieldName ?? $fieldConfig->name;
                        $fieldContent = "item.{$actualFieldName}";
                    } else {
                        // For relationship options, use the relationship object with optional chaining
                        $relationshipName = $fieldConfig->name;
                        $labelField = $fieldConfig->labelField ?? 'name';
                        $fieldContent = "item.{$relationshipName}?.{$labelField}";
                    }
                } else {
                    // For regular fields, use the actual field name
                    $actualFieldName = $fieldConfig->fieldName ?? $fieldConfig->name;

                    if ($fieldConfig->inputType === 'file') {
                        // For file fields, create a link
                        $fieldContent = "item.{$actualFieldName} ? <a href={item.{$actualFieldName}} target=\"_blank\" rel=\"noopener noreferrer\" className=\"text-blue-600 hover:underline\">View File</a> : 'No file'";
                    } else {
                        $fieldContent = "item.{$actualFieldName}";

                        if ($fieldConfig->type === 'boolean') {
                            $fieldContent .= ' ? <CircleCheck className="text-green-400" /> : <CircleX className="text-red-400" />';
                        }
                    }
                }

                return Str::of("<TableCell >{{$fieldContent}}</TableCell>")->prepend(Str::of(' ')->repeat(16));
            })
            ->implode(PHP_EOL);
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getSelectTypes(Collection $fields): string
    {
        return $fields
            ->filter(function (FieldInterface $field): bool {
                $fieldConfig = $field->getConfig();

                // Only generate types for relationship-based select fields (non-array options)
                return $fieldConfig->inputType === 'select' && ! is_array($fieldConfig->options);
            })
            ->map(fn (FieldInterface $field): string => $field->getTypeDefinition())
            ->filter()
            ->implode(PHP_EOL.PHP_EOL);
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

    private function hasSearchableFields(): bool
    {
        return collect($this->config->fields)
            ->some(fn (FieldConfigDTO $field): bool => $field->searchable);
    }

    private function getSearchPlaceholder(): string
    {
        $searchableFields = collect($this->config->fields)
            ->filter(fn (FieldConfigDTO $field): bool => $field->searchable)
            ->map(fn (FieldConfigDTO $field) => Str::of($field->name)->replace('_', ' ')->title())
            ->take(2)
            ->implode(', ');

        if (empty($searchableFields)) {
            return "Search {$this->config->model}s...";
        }

        return "Search by {$searchableFields}...";
    }
}
