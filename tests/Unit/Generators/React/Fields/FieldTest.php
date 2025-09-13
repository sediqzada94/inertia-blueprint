<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\Fields;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Field;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\FieldRenderer;

class FieldTest extends TestCase
{
    private \Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\FieldRenderer&\Mockery\MockInterface $mockRenderer;

    private FieldConfigDTO $fieldConfig;

    private Field $field;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRenderer = Mockery::mock(FieldRenderer::class);
        $this->fieldConfig = new FieldConfigDTO(
            name: 'test_field',
            type: 'string',
            inputType: 'text'
        );
        $this->field = new Field($this->fieldConfig, $this->mockRenderer);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_name_returns_field_name(): void
    {
        $this->assertEquals('test_field', $this->field->getName());
    }

    public function test_get_field_name_returns_field_name_when_set(): void
    {
        $config = new FieldConfigDTO(
            name: 'user_id',
            type: 'string',
            inputType: 'text',
            fieldName: 'author'
        );
        $field = new Field($config, $this->mockRenderer);

        $this->assertEquals('author', $field->getFieldName());
    }

    public function test_get_field_name_returns_name_when_field_name_not_set(): void
    {
        $this->assertEquals('test_field', $this->field->getFieldName());
    }

    public function test_get_input_type_returns_input_type(): void
    {
        $this->assertEquals('text', $this->field->getInputType());
    }

    public function test_get_label_converts_snake_case_to_title_case(): void
    {
        $this->assertEquals('Test Field', $this->field->getLabel());
    }

    public function test_get_label_handles_single_word(): void
    {
        $config = new FieldConfigDTO(name: 'title', type: 'string', inputType: 'text');
        $field = new Field($config, $this->mockRenderer);

        $this->assertEquals('Title', $field->getLabel());
    }

    public function test_get_default_value_declaration_for_string_field(): void
    {
        $result = $this->field->getDefaultValueDeclaration();

        $this->assertEquals("test_field: '',", $result);
    }

    public function test_get_default_value_declaration_for_number_field(): void
    {
        $config = new FieldConfigDTO(name: 'price', type: 'number', inputType: 'number');
        $field = new Field($config, $this->mockRenderer);

        $result = $field->getDefaultValueDeclaration();

        $this->assertEquals('price: 0,', $result);
    }

    public function test_get_default_value_declaration_for_integer_field(): void
    {
        $config = new FieldConfigDTO(name: 'count', type: 'integer', inputType: 'number');
        $field = new Field($config, $this->mockRenderer);

        $result = $field->getDefaultValueDeclaration();

        $this->assertEquals('count: 0,', $result);
    }

    public function test_get_default_value_declaration_for_checkbox_field(): void
    {
        $config = new FieldConfigDTO(name: 'is_active', type: 'boolean', inputType: 'checkbox');
        $field = new Field($config, $this->mockRenderer);

        $result = $field->getDefaultValueDeclaration();

        $this->assertEquals('is_active: false as boolean,', $result);
    }

    public function test_get_default_value_declaration_for_file_field(): void
    {
        $config = new FieldConfigDTO(name: 'avatar', type: 'file', inputType: 'file');
        $field = new Field($config, $this->mockRenderer);

        $result = $field->getDefaultValueDeclaration();

        $this->assertEquals('avatar: null as File | null,', $result);
    }

    public function test_get_default_value_declaration_with_field_name_override(): void
    {
        $config = new FieldConfigDTO(
            name: 'user_id',
            type: 'string',
            inputType: 'text',
            fieldName: 'author'
        );
        $field = new Field($config, $this->mockRenderer);

        $result = $field->getDefaultValueDeclaration();

        $this->assertEquals("author: '',", $result);
    }

    public function test_render_wraps_renderer_output_with_field_wrapper(): void
    {
        $rendererOutput = '<Input value="test" />';
        $this->mockRenderer->shouldReceive('render')
            ->once()
            ->with($this->field)
            ->andReturn($rendererOutput);

        $result = $this->field->render();

        $this->assertStringContainsString('<div className="mb-4">', $result);
        $this->assertStringContainsString('<Label htmlFor="test_field"', $result);
        $this->assertStringContainsString('Test Field:', $result);
        $this->assertStringContainsString($rendererOutput, $result);
        $this->assertStringContainsString('{errors.test_field &&', $result);
    }

    public function test_get_type_definition_returns_empty_for_non_select_fields(): void
    {
        $result = $this->field->getTypeDefinition();

        $this->assertEquals('', $result);
    }

    public function test_get_type_definition_returns_interface_for_select_fields(): void
    {
        $config = new FieldConfigDTO(
            name: 'category_id',
            type: 'select',
            inputType: 'select',
            options: 'categories',
            valueField: 'id',
            labelField: 'name'
        );
        $field = new Field($config, $this->mockRenderer);

        $result = $field->getTypeDefinition();

        $this->assertStringContainsString('interface Category', $result);
        $this->assertStringContainsString('id: string', $result);
        $this->assertStringContainsString('name: string', $result);
    }

    public function test_get_input_declaration_returns_empty_for_non_select_fields(): void
    {
        $result = $this->field->getInputDeclaration();

        $this->assertEquals('', $result);
    }

    public function test_get_input_declaration_returns_plural_name_for_select_fields(): void
    {
        $config = new FieldConfigDTO(
            name: 'category',
            type: 'select',
            inputType: 'select',
            options: 'categories'
        );
        $field = new Field($config, $this->mockRenderer);

        $result = $field->getInputDeclaration();

        $this->assertEquals('categories', $result);
    }

    public function test_get_prop_type_declaration_returns_empty_for_non_select_fields(): void
    {
        $result = $this->field->getPropTypeDeclaration();

        $this->assertEquals('', $result);
    }

    public function test_get_prop_type_declaration_returns_typed_array_for_select_fields(): void
    {
        $config = new FieldConfigDTO(
            name: 'category',
            type: 'select',
            inputType: 'select',
            options: 'categories'
        );
        $field = new Field($config, $this->mockRenderer);

        $result = $field->getPropTypeDeclaration();

        $this->assertEquals('categories: Category[]', $result);
    }

    public function test_get_config_returns_field_config(): void
    {
        $result = $this->field->getConfig();

        $this->assertSame($this->fieldConfig, $result);
    }
}
