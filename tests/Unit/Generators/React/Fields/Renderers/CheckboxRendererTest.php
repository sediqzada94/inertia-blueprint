<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\Fields\Renderers;

use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Field;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\CheckboxRenderer;

class CheckboxRendererTest extends TestCase
{
    private CheckboxRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new CheckboxRenderer;
    }

    public function test_renders_checkbox_component(): void
    {
        $config = new FieldConfigDTO(name: 'is_active', type: 'boolean', inputType: 'checkbox');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('<Checkbox', $result);
        $this->assertStringContainsString('id="is_active"', $result);
        $this->assertStringContainsString('checked={data.is_active}', $result);
        $this->assertStringContainsString('onCheckedChange={(checked) => setData(\'is_active\', !!checked)}', $result);
    }

    public function test_handles_field_name_override(): void
    {
        $config = new FieldConfigDTO(
            name: 'is_published',
            type: 'boolean',
            inputType: 'checkbox',
            fieldName: 'published'
        );
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('id="is_published"', $result);
        $this->assertStringContainsString('checked={data.published}', $result);
        $this->assertStringContainsString("setData('published'", $result);
    }

    public function test_renders_with_proper_boolean_conversion(): void
    {
        $config = new FieldConfigDTO(name: 'featured', type: 'boolean', inputType: 'checkbox');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('!!checked', $result);
    }
}
