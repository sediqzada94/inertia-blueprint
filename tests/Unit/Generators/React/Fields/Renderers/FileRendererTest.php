<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\Fields\Renderers;

use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Field;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\Renderers\FileRenderer;

class FileRendererTest extends TestCase
{
    private FileRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new FileRenderer;
    }

    public function test_renders_file_input_for_create_context(): void
    {
        $config = new FieldConfigDTO(name: 'avatar', type: 'file', inputType: 'file');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('<div>', $result);
        $this->assertStringContainsString('<Input', $result);
        $this->assertStringContainsString('id="avatar"', $result);
        $this->assertStringContainsString('type="file"', $result);
        $this->assertStringContainsString('className="hover:cursor-pointer"', $result);
        $this->assertStringContainsString('e.target.files[0]', $result);
        $this->assertStringContainsString("setData('avatar'", $result);
    }

    public function test_renders_file_input_for_edit_context_with_current_file_link(): void
    {
        $config = new FieldConfigDTO(name: 'document', type: 'file', inputType: 'file');
        $field = new Field($config, $this->renderer);
        $this->renderer->setContext('edit');

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('Current file:', $result);
        $this->assertStringContainsString('View Current File', $result);
        $this->assertStringContainsString('data.document && typeof data.document === \'string\'', $result);
        $this->assertStringContainsString('href={data.document}', $result);
        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('rel="noopener noreferrer"', $result);
    }

    public function test_set_context_returns_self_for_chaining(): void
    {
        $result = $this->renderer->setContext('edit');

        $this->assertSame($this->renderer, $result);
    }

    public function test_handles_field_name_override(): void
    {
        $config = new FieldConfigDTO(
            name: 'profile_image',
            type: 'file',
            inputType: 'file',
            fieldName: 'image'
        );
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('id="profile_image"', $result);
        $this->assertStringContainsString("setData('image'", $result);
    }

    public function test_renders_proper_file_change_handler(): void
    {
        $config = new FieldConfigDTO(name: 'upload', type: 'file', inputType: 'file');
        $field = new Field($config, $this->renderer);

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('onChange={(e: React.ChangeEvent<HTMLInputElement>)', $result);
        $this->assertStringContainsString('e.target.files && e.target.files[0]', $result);
        $this->assertStringContainsString('setData(\'upload\', e.target.files[0])', $result);
    }

    public function test_edit_context_shows_current_file_with_field_name_override(): void
    {
        $config = new FieldConfigDTO(
            name: 'attachment_file',
            type: 'file',
            inputType: 'file',
            fieldName: 'attachment'
        );
        $field = new Field($config, $this->renderer);
        $this->renderer->setContext('edit');

        $result = $this->renderer->render($field);

        $this->assertStringContainsString('data.attachment && typeof data.attachment === \'string\'', $result);
        $this->assertStringContainsString('href={data.attachment}', $result);
    }

    public function test_create_context_does_not_show_current_file_link(): void
    {
        $config = new FieldConfigDTO(name: 'photo', type: 'file', inputType: 'file');
        $field = new Field($config, $this->renderer);
        $this->renderer->setContext('create');

        $result = $this->renderer->render($field);

        $this->assertStringNotContainsString('Current file:', $result);
        $this->assertStringNotContainsString('View Current File', $result);
    }
}
