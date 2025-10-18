<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\Fields\Renderers;

use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Field;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\SelectRenderer;

class SelectRendererTest extends TestCase
{
    private SelectRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new SelectRenderer;
    }

    public function test_renders_select_component_for_create_context(): void
    {
        $config = new FieldConfigDTO(
            name: 'category',
            type: 'select',
            inputType: 'select',
            fieldName: 'category_id',
            options: 'categories',
            valueField: 'id',
            labelField: 'name'

        );
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('<Select onValueChange={(value: string) => setData(\'category_id\', value)}>', $result);
        $this->assertStringContainsString('<SelectTrigger className="hover:cursor-pointer">', $result);
        $this->assertStringContainsString('<SelectValue placeholder="Category" />', $result);
        $this->assertStringContainsString('<SelectContent>', $result);
        $this->assertStringContainsString('{categories.map((item) => (', $result);
        $this->assertStringContainsString('key={item.id}', $result);
        $this->assertStringContainsString('value={item.id.toString()}', $result);
        $this->assertStringContainsString('{item.name}', $result);
    }

    public function test_renders_select_component_for_edit_context_with_default_value(): void
    {
        $config = new FieldConfigDTO(
            name: 'status',
            type: 'select',
            inputType: 'select',
            options: 'statuses',
            valueField: 'value',
            labelField: 'label'
        );
        $field = new Field($config, $this->renderer);
        $this->renderer->setContext('edit');

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('defaultValue={data.status.toString()}', $result);
        $this->assertStringContainsString("setData('status', value)", $result);
        $this->assertStringContainsString('key={item.value}', $result);
        $this->assertStringContainsString('value={item.value.toString()}', $result);
        $this->assertStringContainsString('{item.label}', $result);
    }

    public function test_set_context_returns_self_for_chaining(): void
    {
        $result = $this->renderer->setContext('edit');

        $this->assertSame($this->renderer, $result);
    }

    public function test_handles_field_name_override(): void
    {
        $config = new FieldConfigDTO(
            name: 'user_id',
            type: 'select',
            inputType: 'select',
            fieldName: 'author',
            options: 'users',
            valueField: 'id',
            labelField: 'name'
        );
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString("setData('author', value)", $result);
    }

    public function test_renders_with_array_options(): void
    {
        $config = new FieldConfigDTO(
            name: 'priority',
            type: 'string',
            inputType: 'select',
            options: [
                ['value' => 'high', 'label' => 'High Priority'],
                ['value' => 'medium', 'label' => 'Medium Priority'],
                ['value' => 'low', 'label' => 'Low Priority'],
            ],
            valueField: 'value',
            labelField: 'label'
        );
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString("{priorities.map((item) => (", $result);
        $this->assertStringContainsString("<SelectItem key={item.value} value={item.value.toString()}>", $result);
        $this->assertStringContainsString(" {item.label}", $result);
    }

    public function test_renders_placeholder_with_field_label(): void
    {
        $config = new FieldConfigDTO(
            name: 'department_id',
            type: 'select',
            inputType: 'select',
            options: 'departments',
            valueField: 'id',
            labelField: 'name'
        );
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('placeholder="Department Id"', $result);
    }

    public function test_edit_context_with_field_name_override(): void
    {
        $config = new FieldConfigDTO(
            name: 'category_id',
            type: 'select',
            inputType: 'select',
            fieldName: 'category',
            options: 'categories',
            valueField: 'id',
            labelField: 'name'
        );
        $field = new Field($config, $this->renderer);
        $this->renderer->setContext('edit');

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('defaultValue={data.category.toString()}', $result);
    }

    public function test_create_context_does_not_have_default_value(): void
    {
        $config = new FieldConfigDTO(
            name: 'type',
            type: 'select',
            inputType: 'select',
            options: 'types',
            valueField: 'id',
            labelField: 'name'
        );
        $field = new Field($config, $this->renderer);
        $this->renderer->setContext('create');

        $result = $this->renderer->render($field);

        $this->assertStringNotContainsString('defaultValue=', $result);
    }
}
