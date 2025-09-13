<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers;

use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

class CheckboxRenderer implements FieldRenderer
{
    public function render(FieldInterface $field): string
    {
        $fieldName = $field->getFieldName();

        return <<<JSX
<div className="flex items-center mt-2">
  <Checkbox
    id="{$field->getName()}"
    checked={data.{$fieldName}}
    onCheckedChange={(checked) => setData('{$fieldName}', !!checked)}
    className="hover:cursor-pointer"
  />
</div>
JSX;
    }
}
