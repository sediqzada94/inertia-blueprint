<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers;

use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

class TextRenderer implements FieldRenderer
{
    public function render(FieldInterface $field): string
    {
        $fieldName = $field->getFieldName();
        $inputType = $field->getInputType();
        $onChange = $this->getOnChangeHandler($field);

        return <<<JSX
<Input
  id="{$field->getName()}"
  name="{$field->getName()}"
  value={data.{$fieldName}}
  onChange={$onChange}
  type="{$inputType}"
/>
JSX;
    }

    private function getOnChangeHandler(FieldInterface $field): string
    {
        $fieldName = $field->getFieldName();
        $config = $field->getConfig();

        return match ($config->type) {
            'integer', 'number' => "{(e: React.ChangeEvent<HTMLInputElement>) => setData('{$fieldName}', Number(e.target.value))}",
            default => "{(e: React.ChangeEvent<HTMLInputElement>) => setData('{$fieldName}', e.target.value)}"
        };
    }
}
