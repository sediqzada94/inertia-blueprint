<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\Fields\Renderers;

use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Field;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\TextRenderer;

class TextRendererTest extends TestCase
{
    private TextRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new TextRenderer;
    }

    public function test_renders_text_input_for_string_field(): void
    {
        $config = new FieldConfigDTO(name: 'title', type: 'string', inputType: 'text');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('<Input', $result);
        $this->assertStringContainsString('id="title"', $result);
        $this->assertStringContainsString('name="title"', $result);
        $this->assertStringContainsString('value={data.title}', $result);
        $this->assertStringContainsString('type="text"', $result);
        $this->assertStringContainsString('e.target.value', $result);
    }

    public function test_renders_number_input_with_number_handler(): void
    {
        $config = new FieldConfigDTO(name: 'price', type: 'number', inputType: 'number');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('type="number"', $result);
        $this->assertStringContainsString('Number(e.target.value)', $result);
        $this->assertStringContainsString("setData('price'", $result);
    }

    public function test_renders_integer_input_with_number_handler(): void
    {
        $config = new FieldConfigDTO(name: 'count', type: 'integer', inputType: 'number');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('Number(e.target.value)', $result);
        $this->assertStringContainsString("setData('count'", $result);
    }

    public function test_renders_email_input(): void
    {
        $config = new FieldConfigDTO(name: 'email', type: 'string', inputType: 'email');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('type="email"', $result);
        $this->assertStringContainsString('e.target.value', $result);
    }

    public function test_renders_password_input(): void
    {
        $config = new FieldConfigDTO(name: 'password', type: 'string', inputType: 'password');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('type="password"', $result);
        $this->assertStringContainsString('e.target.value', $result);
    }

    public function test_handles_field_name_override(): void
    {
        $config = new FieldConfigDTO(
            name: 'user_id',
            type: 'string',
            inputType: 'text',
            fieldName: 'author'
        );
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('id="user_id"', $result);
        $this->assertStringContainsString('name="user_id"', $result);
        $this->assertStringContainsString('value={data.author}', $result);
        $this->assertStringContainsString("setData('author'", $result);
    }

    public function test_renders_date_input(): void
    {
        $config = new FieldConfigDTO(name: 'birth_date', type: 'string', inputType: 'date');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('type="date"', $result);
        $this->assertStringContainsString('e.target.value', $result);
    }
}
