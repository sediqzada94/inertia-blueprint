<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\Fields;

use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldFactory;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;

class FieldFactoryTest extends TestCase
{
    public function test_creates_text_field_by_default(): void
    {
        $config = new FieldConfigDTO('name', 'string', 'text');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('text', $field->getInputType());
        $this->assertEquals('name', $field->getName());
    }

    public function test_creates_textarea_field(): void
    {
        $config = new FieldConfigDTO('description', 'text', 'textarea');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('textarea', $field->getInputType());
        $this->assertEquals('description', $field->getName());
    }

    public function test_creates_checkbox_field(): void
    {
        $config = new FieldConfigDTO('is_active', 'boolean', 'checkbox');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('checkbox', $field->getInputType());
        $this->assertEquals('is_active', $field->getName());
    }

    public function test_creates_select_field(): void
    {
        $config = new FieldConfigDTO('category_id', 'integer', 'select', options: ['categories']);

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('select', $field->getInputType());
        $this->assertEquals('category_id', $field->getName());
    }

    public function test_creates_file_field(): void
    {
        $config = new FieldConfigDTO('attachment', 'string', 'file');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('file', $field->getInputType());
        $this->assertEquals('attachment', $field->getName());
    }

    public function test_creates_field_with_context(): void
    {
        $config = new FieldConfigDTO('category_id', 'integer', 'select', options: ['categories']);

        $field = FieldFactory::create($config, 'edit');

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('select', $field->getInputType());
    }

    public function test_handles_unknown_input_type(): void
    {
        $config = new FieldConfigDTO('custom_field', 'string', 'unknown_type');

        $field = FieldFactory::create($config);

        // Should default to text renderer
        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('unknown_type', $field->getInputType());
        $this->assertEquals('custom_field', $field->getName());
    }

    public function test_creates_email_field_as_text(): void
    {
        $config = new FieldConfigDTO('email', 'string', 'email');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('email', $field->getInputType());
        $this->assertEquals('email', $field->getName());
    }

    public function test_creates_password_field_as_text(): void
    {
        $config = new FieldConfigDTO('password', 'string', 'password');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('password', $field->getInputType());
        $this->assertEquals('password', $field->getName());
    }

    public function test_creates_number_field_as_text(): void
    {
        $config = new FieldConfigDTO('age', 'integer', 'number');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('number', $field->getInputType());
        $this->assertEquals('age', $field->getName());
    }

    public function test_creates_date_field_as_text(): void
    {
        $config = new FieldConfigDTO('birth_date', 'date', 'date');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('date', $field->getInputType());
        $this->assertEquals('birth_date', $field->getName());
    }

    public function test_creates_datetime_field_as_text(): void
    {
        $config = new FieldConfigDTO('created_at', 'datetime', 'datetime');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('datetime', $field->getInputType());
        $this->assertEquals('created_at', $field->getName());
    }

    public function test_context_defaults_to_create(): void
    {
        $config = new FieldConfigDTO('category_id', 'integer', 'select', options: ['categories']);

        $field = FieldFactory::create($config, null);

        $this->assertInstanceOf(FieldInterface::class, $field);
    }

    public function test_field_with_field_name_override(): void
    {
        $config = new FieldConfigDTO('user_id', 'integer', 'select',
            fieldName: 'author', options: ['users']);

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('user_id', $field->getName());
        $this->assertEquals('author', $field->getFieldName());
    }

    public function test_field_with_select_options(): void
    {
        $config = new FieldConfigDTO('category_id', 'integer', 'select',
            options: ['categories'], valueField: 'id', labelField: 'name');

        $field = FieldFactory::create($config);

        $this->assertInstanceOf(FieldInterface::class, $field);
        $this->assertEquals('select', $field->getInputType());
        $this->assertEquals(['categories'], $field->getConfig()->options);
    }
}
