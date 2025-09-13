<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers;

use Illuminate\Support\Str;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

class SelectRenderer implements FieldRenderer
{
    private ?string $context = null;

    public function setContext(string $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function render(FieldInterface $field): string
    {
        $options = $this->renderOptions($field);
        $fieldName = $field->getFieldName();
        $defaultValue = $this->getDefaultValue($field);

        return <<<JSX
<Select onValueChange={(value: string) => setData('{$fieldName}', value)}{$defaultValue}>
  <SelectTrigger className="hover:cursor-pointer">
    <SelectValue placeholder="{$field->getLabel()}" />
  </SelectTrigger>
  <SelectContent>
    {$options}
  </SelectContent>
</Select>
JSX;
    }

    private function getDefaultValue(FieldInterface $field): string
    {
        if ($this->context === 'edit') {
            $fieldName = $field->getFieldName();

            return " defaultValue={data.{$fieldName}.toString()}";
        }

        return '';
    }

    private function renderOptions(FieldInterface $field): string
    {
        $config = $field->getConfig();
        $pluralName = Str::of($field->getName())->plural();

        $valueKey = is_string($config->valueField) ? $config->valueField : 'value';
        $labelKey = is_string($config->labelField) ? $config->labelField : 'label';

        if (is_array($config->options)) {
            /** @var array<int, array<string, scalar|null>> $options */
            $options = array_values(array_filter($config->options, 'is_array'));

            return collect($options)
                ->map(fn (array $option): string => sprintf(
                    "    <SelectItem value='%s'>%s</SelectItem>",
                    str_replace("'", '&#39;', (string) ($option[$valueKey] ?? '')),
                    htmlspecialchars((string) ($option[$labelKey] ?? ''), ENT_QUOTES, 'UTF-8')
                ))
                ->implode(PHP_EOL);
        }

        return <<<JSX
{{$pluralName}.map((item) => (
  <SelectItem key={item.{$config->valueField}} value={item.{$config->valueField}.toString()}>
    {item.{$config->labelField}}
  </SelectItem>
))}
JSX;
    }
}
