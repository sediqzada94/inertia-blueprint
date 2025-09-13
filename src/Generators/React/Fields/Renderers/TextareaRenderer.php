<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers;

use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

class TextareaRenderer implements FieldRenderer
{
    public function render(FieldInterface $field): string
    {
        $fieldName = $field->getFieldName();

        return <<<JSX
<Textarea
  value={data.{$fieldName}}
  onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('{$fieldName}', e.target.value)}
  className="min-h-32"
/>
JSX;
    }
}
