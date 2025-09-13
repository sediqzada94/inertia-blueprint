<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\Fields\Renderers;

use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Field;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\TextareaRenderer;

class TextareaRendererTest extends TestCase
{
    private TextareaRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new TextareaRenderer;
    }

    public function test_renders_textarea_component(): void
    {
        $config = new FieldConfigDTO(name: 'description', type: 'text', inputType: 'textarea');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('<Textarea', $result);
        $this->assertStringContainsString('value={data.description}', $result);
        $this->assertStringContainsString('onChange={(e: React.ChangeEvent<HTMLTextAreaElement>)', $result);
        $this->assertStringContainsString("setData('description', e.target.value)", $result);
        $this->assertStringContainsString('className="min-h-32"', $result);
    }

    public function test_handles_field_name_override(): void
    {
        $config = new FieldConfigDTO(
            name: 'post_content',
            type: 'text',
            inputType: 'textarea',
            fieldName: 'content'
        );
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('value={data.content}', $result);
        $this->assertStringContainsString("setData('content'", $result);
    }

    public function test_renders_with_proper_typescript_types(): void
    {
        $config = new FieldConfigDTO(name: 'notes', type: 'text', inputType: 'textarea');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('React.ChangeEvent<HTMLTextAreaElement>', $result);
    }
}
