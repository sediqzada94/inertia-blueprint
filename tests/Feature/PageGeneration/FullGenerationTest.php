<?php

namespace Sediqzada\InertiaBlueprint\Tests\Feature\PageGeneration;

use Illuminate\Support\Facades\File;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Services\ConfigLoaderService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class FullGenerationTest extends TestCase
{
    private ConfigLoaderService $configLoader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configLoader = new ConfigLoaderService;
    }

    public function test_loads_and_validates_complex_config(): void
    {
        $config = [
            'model' => 'Post',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'inputType' => 'text'],
                ['name' => 'content', 'type' => 'text', 'inputType' => 'textarea'],
                ['name' => 'category_id', 'type' => 'integer', 'inputType' => 'select', 'options' => ['categories']],
            ],
            'pages' => ['index', 'create', 'edit', 'view'],
            'routes' => [
                'index' => 'posts.index',
                'store' => 'posts.store',
            ],
        ];

        File::shouldReceive('exists')
            ->once()
            ->with('/path/to/config.json')
            ->andReturn(true);

        File::shouldReceive('get')
            ->once()
            ->with('/path/to/config.json')
            ->andReturn(json_encode($config));

        $result = $this->configLoader->load('/path/to/config.json');

        $this->assertInstanceOf(PageConfigDTO::class, $result);
        $this->assertEquals('Post', $result->model);
        $this->assertCount(3, $result->fields);
        $this->assertEquals(['index', 'create', 'edit', 'view'], $result->pages);
        $this->assertIsArray($result->routes);
    }

    public function test_validates_field_types_and_configurations(): void
    {
        $config = [
            'model' => 'User',
            'fields' => [
                ['name' => 'name', 'type' => 'string', 'inputType' => 'text'],
                ['name' => 'email', 'type' => 'string', 'inputType' => 'email'],
                ['name' => 'role_id', 'type' => 'integer', 'inputType' => 'select', 'options' => ['roles'], 'field_name' => 'role'],
            ],
            'pages' => ['index', 'create'],
        ];

        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(json_encode($config));

        $result = $this->configLoader->load('test.json');

        $this->assertCount(3, $result->fields);

        // Test field configurations
        $roleField = $result->fields[2];
        $this->assertEquals('role_id', $roleField->name);
        $this->assertEquals('role', $roleField->fieldName);
        $this->assertEquals('role', $roleField->getNameForUse());
        $this->assertEquals(['roles'], $roleField->options);
    }

    public function test_handles_minimal_configuration(): void
    {
        $config = [
            'model' => 'SimpleModel',
            'fields' => [
                ['name' => 'name', 'type' => 'string', 'inputType' => 'text'],
            ],
            'pages' => ['index'],
        ];

        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(json_encode($config));

        $result = $this->configLoader->load('simple.json');

        $this->assertEquals('SimpleModel', $result->model);
        $this->assertCount(1, $result->fields);
        $this->assertEquals(['index'], $result->pages);
        $this->assertNull($result->routes);
    }
}
