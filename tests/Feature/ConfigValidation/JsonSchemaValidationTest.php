<?php

namespace Sediqzada\InertiaBlueprint\Tests\Feature\ConfigValidation;

use Illuminate\Support\Facades\File;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Services\ConfigLoaderService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class JsonSchemaValidationTest extends TestCase
{
    private ConfigLoaderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConfigLoaderService;
    }

    public function test_validates_minimal_required_config(): void
    {
        $config = [
            'model' => 'Post',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'inputType' => 'text'],
            ],
            'pages' => ['index'],
        ];

        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(json_encode($config));

        $result = $this->service->load('test.json');

        $this->assertInstanceOf(PageConfigDTO::class, $result);
        $this->assertEquals('Post', $result->model);
    }

    public function test_validates_complex_config_with_all_options(): void
    {
        $config = [
            'model' => 'BlogPost',
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'string',
                    'inputType' => 'text',
                ],
                [
                    'name' => 'category_id',
                    'type' => 'integer',
                    'inputType' => 'select',
                    'field_name' => 'category',
                    'options' => ['categories'],
                    'valueField' => 'id',
                    'labelField' => 'name',
                ],
                [
                    'name' => 'content',
                    'type' => 'text',
                    'inputType' => 'textarea',
                ],
                [
                    'name' => 'published_at',
                    'type' => 'datetime',
                    'inputType' => 'datetime',
                ],
            ],
            'pages' => ['index', 'create', 'edit', 'view'],
            'routes' => [
                'index' => 'blog.posts.index',
                'store' => 'blog.posts.store',
                'edit' => 'blog.posts.edit',
                'show' => 'blog.posts.show',
                'update' => 'blog.posts.update',
                'destroy' => 'blog.posts.destroy',
            ],
        ];

        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(json_encode($config));

        $result = $this->service->load('test.json');

        $this->assertInstanceOf(PageConfigDTO::class, $result);
        $this->assertEquals('BlogPost', $result->model);
        $this->assertCount(4, $result->fields);
        $this->assertEquals(['index', 'create', 'edit', 'view'], $result->pages);
        $this->assertIsArray($result->routes);
    }

    public function test_validates_different_field_types(): void
    {
        $config = [
            'model' => 'User',
            'fields' => [
                ['name' => 'name', 'type' => 'string', 'inputType' => 'text'],
                ['name' => 'email', 'type' => 'string', 'inputType' => 'email'],
                ['name' => 'age', 'type' => 'integer', 'inputType' => 'number'],
                ['name' => 'bio', 'type' => 'text', 'inputType' => 'textarea'],
                ['name' => 'is_active', 'type' => 'boolean', 'inputType' => 'checkbox'],
                ['name' => 'birth_date', 'type' => 'date', 'inputType' => 'date'],
            ],
            'pages' => ['index'],
        ];

        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(json_encode($config));

        $result = $this->service->load('test.json');

        $this->assertCount(6, $result->fields);

        $fieldTypes = array_map(fn (\Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO $field): string => $field->inputType, $result->fields);
        $this->assertEquals(['text', 'email', 'number', 'textarea', 'checkbox', 'date'], $fieldTypes);
    }

    public function test_validates_different_page_combinations(): void
    {
        $testCases = [
            ['index'],
            ['create'],
            ['edit'],
            ['view'],
            ['index', 'create'],
            ['index', 'create', 'edit'],
            ['index', 'create', 'edit', 'view'],
        ];

        foreach ($testCases as $i => $pages) {
            $config = [
                'model' => 'Test',
                'fields' => [['name' => 'name', 'type' => 'string', 'inputType' => 'text']],
                'pages' => $pages,
            ];

            File::shouldReceive('exists')
                ->once()
                ->with("test{$i}.json")
                ->andReturn(true);

            File::shouldReceive('get')
                ->once()
                ->with("test{$i}.json")
                ->andReturn(json_encode($config));

            $result = $this->service->load("test{$i}.json");
            $this->assertEquals($pages, $result->pages, 'Failed for pages: '.implode(', ', $pages));
        }
    }
}
