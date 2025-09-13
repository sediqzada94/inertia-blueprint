<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Core;

use Illuminate\Support\Facades\File;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Enums\LanguageEnum;
use Sediqzada\InertiaBlueprint\Services\ConfigLoaderService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class BlueprintProcessingTest extends TestCase
{
    private ConfigLoaderService $configLoader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configLoader = new ConfigLoaderService;
    }

    public function test_processes_complete_blueprint_configuration(): void
    {
        $blueprint = [
            'model' => 'BlogPost',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'inputType' => 'text', 'searchable' => true],
                ['name' => 'content', 'type' => 'text', 'inputType' => 'textarea', 'searchable' => true],
                ['name' => 'published_at', 'type' => 'datetime', 'inputType' => 'text'],
                ['name' => 'is_featured', 'type' => 'boolean', 'inputType' => 'checkbox'],
                [
                    'name' => 'category_id',
                    'type' => 'select',
                    'inputType' => 'select',
                    'options' => ['categories'],
                    'valueField' => 'id',
                    'labelField' => 'name',
                ],
            ],
            'pages' => ['index', 'create', 'edit', 'show'],
            'routes' => [
                'index' => 'blog.posts.index',
                'store' => 'blog.posts.store',
                'edit' => 'blog.posts.edit',
                'update' => 'blog.posts.update',
                'show' => 'blog.posts.show',
                'destroy' => 'blog.posts.destroy',
            ],
        ];

        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('get')->once()->andReturn(json_encode($blueprint));

        $config = $this->configLoader->load('/path/to/blueprint.json');

        $this->assertInstanceOf(PageConfigDTO::class, $config);
        $this->assertEquals('BlogPost', $config->model);
        $this->assertCount(5, $config->fields);
        $this->assertEquals(['index', 'create', 'edit', 'show'], $config->pages);

        // Verify field types are correctly processed
        $this->assertInstanceOf(FieldConfigDTO::class, $config->fields[0]);
        $this->assertEquals('title', $config->fields[0]->name);
        $this->assertTrue($config->fields[0]->searchable);

        // Verify select field is correctly processed
        $selectField = $config->fields[4];
        $this->assertEquals('category_id', $selectField->name);
        $this->assertEquals('select', $selectField->inputType);
        $this->assertEquals(['categories'], $selectField->options);
    }

    public function test_validates_field_configuration_integrity(): void
    {
        $blueprint = [
            'model' => 'Product',
            'fields' => [
                ['name' => 'name', 'type' => 'string', 'inputType' => 'text'],
                ['name' => 'price', 'type' => 'number', 'inputType' => 'number'],
                ['name' => 'description', 'type' => 'text', 'inputType' => 'textarea'],
                ['name' => 'is_active', 'type' => 'boolean', 'inputType' => 'checkbox'],
            ],
            'pages' => ['index', 'create'],
        ];

        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('get')->once()->andReturn(json_encode($blueprint));

        $config = $this->configLoader->load('/path/to/blueprint.json');

        foreach ($config->fields as $field) {
            $this->assertNotEmpty($field->name);
            $this->assertNotEmpty($field->type);
            $this->assertNotEmpty($field->inputType);
            $this->assertIsBool($field->searchable);
        }
    }

    public function test_language_enum_integration(): void
    {
        $this->assertEquals('ts', LanguageEnum::TS->value);
        $this->assertEquals('tsx', LanguageEnum::TS->extension());

        // Verify only TypeScript is supported
        $cases = LanguageEnum::cases();
        $this->assertCount(1, $cases);
        $this->assertEquals(LanguageEnum::TS, $cases[0]);
    }

    public function test_handles_minimal_valid_configuration(): void
    {
        $blueprint = [
            'model' => 'SimpleModel',
            'fields' => [
                ['name' => 'name', 'type' => 'string', 'inputType' => 'text'],
            ],
            'pages' => ['index'],
        ];

        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('get')->once()->andReturn(json_encode($blueprint));

        $config = $this->configLoader->load('/path/to/blueprint.json');

        $this->assertEquals('SimpleModel', $config->model);
        $this->assertCount(1, $config->fields);
        $this->assertEquals(['index'], $config->pages);
        $this->assertNull($config->routes);
    }
}
