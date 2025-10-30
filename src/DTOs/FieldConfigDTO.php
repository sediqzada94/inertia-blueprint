<?php

namespace Sediqzada\InertiaBlueprint\DTOs;

class FieldConfigDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $inputType,
        public readonly ?string $fieldName = null,
        public readonly mixed $options = null,
        public readonly ?string $valueField = null,
        public readonly ?string $labelField = null,
        public readonly bool $searchable = false
    ) {
        $this->validate();
    }

    /**
     * @param array{
     *     name: string,
     *     type: string,
     *     inputType: string,
     *     field_name?: string|null,
     *     options?: array<mixed>|null,
     *     valueField?: string|null,
     *     labelField?: string|null,
     *     searchable?: bool
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
            inputType: $data['inputType'],
            fieldName: $data['fieldName'] ?? null,
            options: $data['options'] ?? null,
            valueField: $data['inputType'] === 'select' ? $data['valueField'] ?? 'id' : null,
            labelField: $data['inputType'] === 'select' ? $data['labelField'] ?? 'name' : null,
            searchable: $data['searchable'] ?? false
        );
    }

    private function validate(): void
    {
        if ($this->inputType === 'select' && empty($this->options)) {
            throw new \InvalidArgumentException('Select fields require options');
        }
    }

    public function getNameForUse(): string
    {
        return $this->fieldName ?? $this->name;
    }
}
