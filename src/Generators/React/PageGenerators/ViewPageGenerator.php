<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\PageGenerators;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

class ViewPageGenerator extends BasePageGenerator
{
    protected string $pageName = 'View';

    /**
     * @param  Collection<int, FieldInterface>  $fields
     * @return array<string, string>
     */
    protected function getReplacements(Collection $fields): array
    {
        return [
            '{{ model }}' => $this->config->model,
            '{{ modelCamel }}' => $this->getModelCamel(),
            '{{ fields }}' => $this->getFields($fields),
            '{{ viewFields }}' => $this->getViewFields($fields),
            '{{ routeEdit }}' => $this->resolveRoute($this->config->routes['edit'] ?? null, 'edit'),
            '{{ routeIndex }}' => $this->resolveRoute($this->config->routes['index'] ?? null, 'index'),
            '{{ selectTypes }}' => $this->getSelectTypes($fields),
        ];
    }

    /**
     * @param  Collection<int, FieldInterface>  $fields
     */
    private function getFields(Collection $fields): string
    {
        return $fields
            ->flatMap(function (FieldInterface $field): array {
                $fieldConfig = $field->getConfig();
                $fieldName = $fieldConfig->fieldName ?? $fieldConfig->name;
                $output = ["    {$fieldName}: {$this->getTypeScriptType($fieldConfig->type)}"];

                // Only add relationship type for non-array options
                if ($fieldConfig->inputType === 'select' && ! is_array($fieldConfig->options)) {
                    $output[] = "    {$fieldConfig->name}: ".Str::of($fieldConfig->name)->title();
                }

                return $output;
            })
            ->implode(PHP_EOL);
    }

    /**
     * @param  Collection<int, FieldInterface>  $fields
     */
    private function getViewFields(Collection $fields): string
    {
        $modelVar = $this->getModelCamel();

        $gridFields = $fields->filter(fn (FieldInterface $field): bool => $field->getConfig()->inputType !== 'textarea');
        $textareaFields = $fields->filter(fn (FieldInterface $field): bool => $field->getConfig()->inputType === 'textarea');

        $gridFieldsHtml = $gridFields
            ->map(function (FieldInterface $field) use ($modelVar): string {
                $fieldConfig = $field->getConfig();
                $nameTitle = Str::of($fieldConfig->name)->replace('_', ' ')->title();

                if ($fieldConfig->inputType === 'file') {
                    // For file fields, create a link
                    $fieldAccess = "{$modelVar}.{$fieldConfig->name} ? <a href={{$modelVar}.{$fieldConfig->name}} target=\"_blank\" rel=\"noopener noreferrer\" className=\"text-blue-600 hover:underline\">View File</a> : 'No file'";
                } else {
                    if ($fieldConfig->inputType === 'select') {
                        // Check if options is an array (static options) or string (relationship)
                        if (is_array($fieldConfig->options)) {
                            // For array options, use the actual field value directly
                            $actualFieldName = $fieldConfig->fieldName ?? $fieldConfig->name;
                            $fieldAccess = "{$modelVar}.{$actualFieldName}";
                        } else {
                            // For relationship options, use the relationship object with label field
                            $labelField = $fieldConfig->labelField ?? 'name';
                            $fieldAccess = "{$modelVar}.{$fieldConfig->name}?.{$labelField}";
                        }
                    } else {
                        $fieldAccess = "{$modelVar}.{$fieldConfig->name}";
                    }

                    if ($fieldConfig->inputType === 'checkbox') {
                        $fieldAccess .= ' ? <CircleCheck className="text-green-400" /> : <CircleX className="text-red-400" />';
                    }
                }

                return <<<JSX
          <div className="mb-4">
            <div className="block text-gray-700 text-sm font-bold mb-2">
              $nameTitle
            </div>
            <div>
              { $fieldAccess }
            </div>
          </div>
JSX;
            })
            ->implode(PHP_EOL);

        $textareaFieldsHtml = $textareaFields
            ->map(function (FieldInterface $field) use ($modelVar): string {
                $fieldConfig = $field->getConfig();
                $nameTitle = Str::of($fieldConfig->name)->replace('_', ' ')->title();
                $fieldAccess = "{$modelVar}.{$fieldConfig->name}";

                return <<<JSX
        <div className="mb-4">
          <div className="block text-gray-700 text-sm font-bold mb-2">
            $nameTitle
          </div>
          <div className="bg-gray-50 p-4 rounded-md">
            { $fieldAccess }
          </div>
        </div>
JSX;
            })
            ->implode(PHP_EOL);

        return $gridFieldsHtml.($textareaFieldsHtml ? PHP_EOL.'        </div>'.PHP_EOL.$textareaFieldsHtml : PHP_EOL.'        </div>');
    }

    /**
     * @param  Collection<int, FieldInterface>  $fields
     */
    protected function getSelectTypes(Collection $fields): string
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
}
