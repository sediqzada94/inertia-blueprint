<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Services\ConfigLoaderService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class ConfigLoaderServiceTest extends TestCase
{
    private ConfigLoaderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConfigLoaderService;
    }

    public function test_loads_valid_json_config(): void
    {
        $jsonContent = json_encode([
            'model' => 'Post',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'inputType' => 'text'],
            ],
            'pages' => ['index', 'create'],
        ]);

        File::shouldReceive('exists')
            ->once()
            ->with('/path/to/config.json')
            ->andReturn(true);

        File::shouldReceive('get')
            ->once()
            ->with('/path/to/config.json')
            ->andReturn($jsonContent);

        $result = $this->service->load('/path/to/config.json');

        $this->assertInstanceOf(PageConfigDTO::class, $result);
        $this->assertEquals('Post', $result->model);
        $this->assertCount(1, $result->fields);
        $this->assertEquals(['index', 'create'], $result->pages);
    }

    public function test_throws_exception_for_non_json_file(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The file must be a JSON type');

        $this->service->load('/path/to/config.yaml');
    }

    public function test_throws_exception_for_missing_file(): void
    {
        File::shouldReceive('exists')
            ->once()
            ->with('/path/to/missing.json')
            ->andReturn(false);

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File not found: /path/to/missing.json');

        $this->service->load('/path/to/missing.json');
    }

    public function test_throws_exception_for_invalid_json(): void
    {
        File::shouldReceive('exists')
            ->once()
            ->with('/path/to/invalid.json')
            ->andReturn(true);

        File::shouldReceive('get')
            ->once()
            ->with('/path/to/invalid.json')
            ->andReturn('{ invalid json }');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Invalid JSON:/');

        $this->service->load('/path/to/invalid.json');
    }

    public function test_throws_exception_for_empty_json_object(): void
    {
        File::shouldReceive('exists')
            ->once()
            ->with('/path/to/empty.json')
            ->andReturn(true);

        File::shouldReceive('get')
            ->once()
            ->with('/path/to/empty.json')
            ->andReturn('{}');

        $this->expectException(\ErrorException::class);

        $this->service->load('/path/to/empty.json');
    }

    public function test_loads_config_with_complex_field_types(): void
    {
        $jsonContent = json_encode([
            'model' => 'Product',
            'fields' => [
                ['name' => 'name', 'type' => 'string', 'inputType' => 'text', 'searchable' => true],
                ['name' => 'description', 'type' => 'text', 'inputType' => 'textarea'],
                ['name' => 'price', 'type' => 'number', 'inputType' => 'number'],
                ['name' => 'is_active', 'type' => 'boolean', 'inputType' => 'checkbox'],
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
                'index' => 'products.index',
                'store' => 'products.store',
                'edit' => 'products.edit',
                'update' => 'products.update',
                'show' => 'products.show',
                'destroy' => 'products.destroy',
            ],
        ]);

        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('get')->once()->andReturn($jsonContent);

        $result = $this->service->load('/path/to/complex.json');

        $this->assertInstanceOf(PageConfigDTO::class, $result);
        $this->assertEquals('Product', $result->model);
        $this->assertCount(5, $result->fields);
        $this->assertEquals(['index', 'create', 'edit', 'show'], $result->pages);

        $routesRaw = $result->routes;
        $routes = is_array($routesRaw) ? $routesRaw : [];

        self::assertArrayHasKey('index', $routes);
    }

    public function test_validates_required_fields_are_present(): void
    {
        $jsonContent = json_encode([
            'model' => 'Post',
            'fields' => [
                ['name' => 'title'], // Missing required 'type' and 'inputType'
            ],
            'pages' => ['index'],
        ]);

        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('get')->once()->andReturn($jsonContent);

        $this->expectException(\ErrorException::class);

        $this->service->load('/path/to/invalid-field.json');
    }

    public function test_handles_unicode_characters_in_config(): void
    {
        $jsonContent = json_encode([
            'model' => 'Artículo',
            'fields' => [
                ['name' => 'título', 'type' => 'string', 'inputType' => 'text'],
                ['name' => 'descripción', 'type' => 'text', 'inputType' => 'textarea'],
            ],
            'pages' => ['index'],
        ], JSON_UNESCAPED_UNICODE);

        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('get')->once()->andReturn($jsonContent);

        $result = $this->service->load('/path/to/unicode.json');

        $this->assertEquals('Artículo', $result->model);
        $this->assertEquals('título', $result->fields[0]->name);
    }

    public function test_handles_large_config_files(): void
    {
        $fields = [];
        for ($i = 1; $i <= 50; $i++) {
            $fields[] = [
                'name' => "field_{$i}",
                'type' => 'string',
                'inputType' => 'text',
            ];
        }

        $jsonContent = json_encode([
            'model' => 'LargeModel',
            'fields' => $fields,
            'pages' => ['index', 'create', 'edit', 'show'],
        ]);

        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('get')->once()->andReturn($jsonContent);

        $result = $this->service->load('/path/to/large.json');

        $this->assertEquals('LargeModel', $result->model);
        $this->assertCount(50, $result->fields);
    }

    public function test_handles_deeply_nested_route_configurations(): void
    {
        $jsonContent = json_encode([
            'model' => 'Post',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'inputType' => 'text'],
            ],
            'pages' => ['index'],
            'routes' => [
                'index' => 'admin.blog.posts.index',
                'store' => 'admin.blog.posts.store',
                'show' => 'admin.blog.posts.show',
                'edit' => 'admin.blog.posts.edit',
                'update' => 'admin.blog.posts.update',
                'destroy' => 'admin.blog.posts.destroy',
            ],
        ]);

        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('get')->once()->andReturn($jsonContent);

        $result = $this->service->load('/path/to/nested-routes.json');

        // Coerce to array for PHPStan
        $routesRaw = $result->routes;
        /** @var array<string,string> $routes */
        $routes = is_array($routesRaw) ? $routesRaw : [];

        // (optional but nice) prove the keys exist
        self::assertArrayHasKey('index', $routes);
        self::assertArrayHasKey('destroy', $routes);

        // Now safe to read offsets
        self::assertSame('admin.blog.posts.index', $routes['index']);
        self::assertSame('admin.blog.posts.destroy', $routes['destroy']);

    }
}
