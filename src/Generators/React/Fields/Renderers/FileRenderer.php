<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers;

use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

class FileRenderer implements FieldRenderer
{
    private ?string $context = null;

    public function setContext(string $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function render(FieldInterface $field): string
    {
        $fieldName = $field->getFieldName();
        $currentFileLink = $this->getCurrentFileLink($field);

        return <<<JSX
<div>
{$currentFileLink}
  <Input
    id="{$field->getName()}"
    onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
      if (e.target.files && e.target.files[0]) {
        setData('{$fieldName}', e.target.files[0]);
      }
    }}
    type="{$field->getInputType()}"
    className="hover:cursor-pointer"
  />
</div>
JSX;
    }

    private function getCurrentFileLink(FieldInterface $field): string
    {
        if ($this->context === 'edit') {
            $fieldName = $field->getFieldName();

            return <<<JSX
  {data.{$fieldName} && typeof data.{$fieldName} === 'string' && (
    <div className="mb-2">
      <span className="text-sm text-gray-600">Current file: </span>
      <a href={data.{$fieldName}} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">
        View Current File
      </a>
    </div>
  )}
JSX;
        }

        return '';
    }
}
