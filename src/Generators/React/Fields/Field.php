<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\Fields;

use Illuminate\Support\Str;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\FieldRenderer;

class Field implements FieldInterface
{
    public function __construct(
        protected FieldConfigDTO $config,
        protected FieldRenderer $renderer
    ) {}

    public function getName(): string
    {
        return $this->config->name;
    }

    public function getFieldName(): string
    {
        return $this->config->fieldName ?? $this->getName();
    }

    public function getInputType(): string
    {
        return $this->config->inputType;
    }

    public function getLabel(): string
    {
        return Str::of($this->getName())->replace('_', ' ')->title();
    }

    public function getDefaultValueDeclaration(): string
    {
        $fieldName = $this->getFieldName();

        return match ($this->config->inputType) {
            'checkbox' => "{$fieldName}: false as boolean,",
            'file' => "{$fieldName}: null as File | null,",
            default => match ($this->config->type) {
                'integer', 'number' => "{$fieldName}: 0,",
                default => "{$fieldName}: '',",
            }
        };
    }

    public function render(): string
    {
        $content = $this->renderer->render($this);

        return $this->wrapField($content);
    }

    protected function wrapField(string $content): string
    {
        // Indent the content properly with 2 spaces to align with Label
        $indentedContent = $this->indentContent($content, 2);

        return <<<JSX
<div className="mb-4">
  <Label htmlFor="{$this->getName()}" className="me-2 hover:cursor-pointer">
    {$this->getLabel()}:
  </Label>
{$indentedContent}
  {errors.{$this->getFieldName()} && <p className="text-red-600 text-sm mt-1">{errors.{$this->getFieldName()}}</p>}
</div>
JSX;
    }

    private function indentContent(string $content, int $spaces): string
    {
        $indent = Str::of(' ')->repeat($spaces);
        $lines = Str::of($content)->trim()->explode("\n");

        return $indent.$lines->implode("\n".$indent);
    }

    public function getTypeDefinition(): string
    {
        if ($this->config->inputType !== 'select') {
            return '';
        }

        $fieldName = Str::of($this->getName())->studly();
        $valueType = $this->getValueFieldType();

        return <<<TS
interface $fieldName {
    {$this->config->valueField}: {$valueType}
    {$this->config->labelField}: string
}
TS;
    }

    private function getValueFieldType(): string
    {
        // Most select values are either string or number, defaulting to string for safety
        return 'string';
    }

    public function getInputDeclaration(): string
    {
        if ($this->config->inputType !== 'select') {
            return '';
        }

        return Str::of($this->getName())->plural();
    }

    public function getPropTypeDeclaration(): string
    {
        if ($this->config->inputType !== 'select') {
            return '';
        }

        return Str::of($this->getName())->plural().': '.Str::of($this->getName())->studly().'[]';
    }

    public function getConfig(): FieldConfigDTO
    {
        return $this->config;
    }
}
