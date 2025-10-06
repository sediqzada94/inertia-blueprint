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

        if (is_array($config->options)) {
            return collect($config->options)
                ->map(fn (array $option): string => "    <SelectItem value='{$option['value']}'>{$option['label']}</SelectItem>",
                )
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
