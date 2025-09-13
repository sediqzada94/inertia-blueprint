<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\DTOs;

use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;

class FieldConfigDTOTest extends TestCase
{
    public function test_creates_from_array_with_basic_field(): void
    {
        $data = [
            'name' => 'title',
            'type' => 'string',
            'inputType' => 'text',
        ];

        $dto = FieldConfigDTO::fromArray($data);

        $this->assertEquals('title', $dto->name);
        $this->assertEquals('string', $dto->type);
        $this->assertEquals('text', $dto->inputType);
        $this->assertNull($dto->fieldName);
        $this->assertNull($dto->options);
    }

    public function test_creates_from_array_with_select_field(): void
    {
        $data = [
            'name' => 'category',
            'type' => 'integer',
            'inputType' => 'select',
            'options' => ['categories'],
            'valueField' => 'id',
            'labelField' => 'name',
        ];

        $dto = FieldConfigDTO::fromArray($data);

        $this->assertEquals('category', $dto->name);
        $this->assertEquals('select', $dto->inputType);
        $this->assertEquals(['categories'], $dto->options);
        $this->assertEquals('id', $dto->valueField);
        $this->assertEquals('name', $dto->labelField);
    }

    public function test_get_name_for_use_returns_field_name_when_set(): void
    {
        $dto = new FieldConfigDTO(
            name: 'user_id',
            type: 'integer',
            inputType: 'text',
            fieldName: 'author'
        );

        $this->assertEquals('author', $dto->getNameForUse());
    }

    public function test_get_name_for_use_returns_name_when_field_name_not_set(): void
    {
        $dto = new FieldConfigDTO(
            name: 'title',
            type: 'string',
            inputType: 'text'
        );

        $this->assertEquals('title', $dto->getNameForUse());
    }

    public function test_select_field_uses_default_value_and_label_fields(): void
    {
        $data = [
            'name' => 'category',
            'type' => 'integer',
            'inputType' => 'select',
            'options' => ['categories'],
        ];

        $dto = FieldConfigDTO::fromArray($data);

        $this->assertEquals('id', $dto->valueField);
        $this->assertEquals('name', $dto->labelField);
    }

    public function test_select_field_validation_requires_options(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Select fields require options');

        new FieldConfigDTO(
            name: 'category',
            type: 'integer',
            inputType: 'select'
        );
    }

    public function test_searchable_field_defaults_to_false(): void
    {
        $dto = new FieldConfigDTO(
            name: 'title',
            type: 'string',
            inputType: 'text'
        );

        $this->assertFalse($dto->searchable);
    }

    public function test_searchable_field_can_be_set_to_true(): void
    {
        $data = [
            'name' => 'title',
            'type' => 'string',
            'inputType' => 'text',
            'searchable' => true,
        ];

        $dto = FieldConfigDTO::fromArray($data);

        $this->assertTrue($dto->searchable);
    }
}
