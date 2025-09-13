<?php

namespace Sediqzada\InertiaBlueprint\Tests\Integration\Generators\React\PageGenerators;

use Mockery;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\CreatePageGenerator;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\EditPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\IndexPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\ViewPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class PageGeneratorIntegrationTest extends TestCase
{
    private \Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService&\Mockery\MockInterface $mockPageGeneratorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockPageGeneratorService = Mockery::mock(PageGeneratorService::class);
    }

    public function test_all_generators_handle_complex_field_configuration(): void
    {
        $complexConfig = new PageConfigDTO(
            model: 'BlogPost',
            fields: [
                new FieldConfigDTO(
                    name: 'title',
                    type: 'string',
                    inputType: 'text',
                    searchable: true
                ),
                new FieldConfigDTO(
                    name: 'slug',
                    type: 'string',
                    inputType: 'text',
                    searchable: true
                ),
                new FieldConfigDTO(
                    name: 'content',
                    type: 'text',
                    inputType: 'textarea'
                ),
                new FieldConfigDTO(
                    name: 'excerpt',
                    type: 'text',
                    inputType: 'textarea'
                ),
                new FieldConfigDTO(
                    name: 'category',
                    type: 'integer',
                    inputType: 'select',
                    fieldName: 'category_id',
                    options: 'categories',
                    valueField: 'id',
                    labelField: 'name'
                ),
                new FieldConfigDTO(
                    name: 'status',
                    type: 'string',
                    inputType: 'select',
                    options: [
                        ['id' => 'draft', 'name' => 'Draft'],
                        ['id' => 'published', 'name' => 'Published'],
                        ['id' => 'archived', 'name' => 'Archived'],
                    ],
                    valueField: 'id',
                    labelField: 'name'
                ),
                new FieldConfigDTO(
                    name: 'is_featured',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
                new FieldConfigDTO(
                    name: 'featured_image',
                    type: 'string',
                    inputType: 'file'
                ),
                new FieldConfigDTO(
                    name: 'published_at',
                    type: 'datetime',
                    inputType: 'datetime-local'
                ),
                new FieldConfigDTO(
                    name: 'view_count',
                    type: 'integer',
                    inputType: 'number'
                ),
            ],
            pages: ['create', 'edit', 'index', 'view'],
            routes: [
                'store' => 'blog.posts.store',
                'update' => 'blog.posts.update',
                'index' => 'blog.posts.index',
                'create' => 'blog.posts.create',
                'edit' => 'blog.posts.edit',
                'show' => 'blog.posts.show',
                'destroy' => 'blog.posts.destroy',
            ]
        );

        $generators = [
            'Create' => new CreatePageGenerator($complexConfig, $this->mockPageGeneratorService),
            'Edit' => new EditPageGenerator($complexConfig, $this->mockPageGeneratorService),
            'Index' => new IndexPageGenerator($complexConfig, $this->mockPageGeneratorService),
            'View' => new ViewPageGenerator($complexConfig, $this->mockPageGeneratorService),
        ];

        foreach ($generators as $pageName => $generator) {
            $this->mockPageGeneratorService
                ->shouldReceive('getOutputPath')
                ->with('BlogPost', $pageName)
                ->once()
                ->andReturn("path/to/{$pageName}.tsx");

            $this->mockPageGeneratorService
                ->shouldReceive('readStub')
                ->with($pageName)
                ->once()
                ->andReturn('stub content');

            $this->mockPageGeneratorService
                ->shouldReceive('resolveRoute')
                ->atLeast()
                ->andReturn('resolved.route');

            $this->mockPageGeneratorService
                ->shouldReceive('replacePlaceholders')
                ->once()
                ->andReturnUsing(function (array $replacements) use ($pageName): string {
                    // All generators should handle the model name
                    $this->assertArrayHasKey('{{ model }}', $replacements);
                    $this->assertEquals('BlogPost', $replacements['{{ model }}']);

                    // Verify page-specific placeholders exist
                    switch ($pageName) {
                        case 'Create':
                            $this->assertArrayHasKey('{{ formFieldsDefault }}', $replacements);
                            $this->assertArrayHasKey('{{ routeStore }}', $replacements);
                            break;
                        case 'Edit':
                            $this->assertArrayHasKey('{{ modelCamel }}', $replacements);
                            $this->assertArrayHasKey('{{ routeUpdate }}', $replacements);
                            break;
                        case 'Index':
                            $this->assertArrayHasKey('{{ tableHeaders }}', $replacements);
                            $this->assertArrayHasKey('{{ tableCells }}', $replacements);
                            $this->assertArrayHasKey('{{ hasSearchableFields }}', $replacements);
                            break;
                        case 'View':
                            $this->assertArrayHasKey('{{ viewFields }}', $replacements);
                            break;
                    }

                    return 'processed content';
                });

            $this->mockPageGeneratorService
                ->shouldReceive('writeToFile')
                ->with("path/to/{$pageName}.tsx", 'processed content')
                ->once();

            // Execute the generator
            $generator->generate();
        }
    }

    public function test_generators_handle_field_validation_edge_cases(): void
    {
        // Test with select field that has empty options (should throw exception)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Select fields require options');

        new FieldConfigDTO(
            name: 'category',
            type: 'integer',
            inputType: 'select',
            options: null
        );
    }

    public function test_generators_handle_minimal_configuration(): void
    {
        $minimalConfig = new PageConfigDTO(
            model: 'SimpleModel',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
            ],
            pages: ['create'],
            routes: []
        );

        $generator = new CreatePageGenerator($minimalConfig, $this->mockPageGeneratorService);

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->once()
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->once()
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->twice()
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturn('content');

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator->generate();
        $this->expectNotToPerformAssertions();
    }

    public function test_generators_handle_unicode_and_special_characters(): void
    {
        $unicodeConfig = new PageConfigDTO(
            model: 'ProductCatalog',
            fields: [
                new FieldConfigDTO(
                    name: 'name_en',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'name_ar',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'price_usd',
                    type: 'number',
                    inputType: 'number'
                ),
                new FieldConfigDTO(
                    name: 'description_with_symbols',
                    type: 'text',
                    inputType: 'textarea'
                ),
            ],
            pages: ['create'],
            routes: []
        );

        $generator = new CreatePageGenerator($unicodeConfig, $this->mockPageGeneratorService);

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->once()
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->once()
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->twice()
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Should handle field names with underscores and special characters
                $formDefaults = $replacements['{{ formFieldsDefault }}'];

                $this->assertIsString($formDefaults);

                $this->assertStringContainsString('name_en', $formDefaults);
                $this->assertStringContainsString('name_ar', $formDefaults);
                $this->assertStringContainsString('price_usd', $formDefaults);
                $this->assertStringContainsString('description_with_symbols', $formDefaults);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator->generate();
    }

    public function test_generators_handle_large_number_of_fields(): void
    {
        $fields = [];
        for ($i = 1; $i <= 50; $i++) {
            $fields[] = new FieldConfigDTO(
                name: "field_{$i}",
                type: 'string',
                inputType: 'text'
            );
        }

        $largeConfig = new PageConfigDTO(
            model: 'LargeModel',
            fields: $fields,
            pages: ['index'],
            routes: []
        );

        $generator = new IndexPageGenerator($largeConfig, $this->mockPageGeneratorService);

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->once()
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->once()
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->times(5)
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Should handle large number of fields without issues
                $tableHeaders = $replacements['{{ tableHeaders }}'];
                $tableCells = $replacements['{{ tableCells }}'];

                $this->assertIsString($tableHeaders);
                $this->assertIsString($tableCells);

                // Should contain all 50 fields (checking a few key ones)
                $this->assertStringContainsString('Field 1', $tableHeaders);
                $this->assertStringContainsString('Field 25', $tableHeaders);
                $this->assertStringContainsString('Field 50', $tableHeaders);
                $this->assertStringContainsString('item.field_1', $tableCells);
                $this->assertStringContainsString('item.field_25', $tableCells);
                $this->assertStringContainsString('item.field_50', $tableCells);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator->generate();
    }

    public function test_generators_consistency_across_field_types(): void
    {
        $fieldTypes = [
            ['text_field', 'string', 'text'],
            ['email_field', 'string', 'email'],
            ['password_field', 'string', 'password'],
            ['number_field', 'integer', 'number'],
            ['decimal_field', 'number', 'number'],
            ['date_field', 'date', 'date'],
            ['datetime_field', 'datetime', 'datetime-local'],
            ['boolean_field', 'boolean', 'checkbox'],
            ['textarea_field', 'text', 'textarea'],
            ['file_field', 'string', 'file'],
        ];

        foreach ($fieldTypes as [$name, $type, $inputType]) {
            $config = new PageConfigDTO(
                model: 'TestModel',
                fields: [
                    new FieldConfigDTO(
                        name: $name,
                        type: $type,
                        inputType: $inputType
                    ),
                ],
                pages: ['create'],
                routes: []
            );

            $generator = new CreatePageGenerator($config, $this->mockPageGeneratorService);

            $this->mockPageGeneratorService
                ->shouldReceive('getOutputPath')
                ->once()
                ->andReturn('path');

            $this->mockPageGeneratorService
                ->shouldReceive('readStub')
                ->once()
                ->andReturn('stub');

            $this->mockPageGeneratorService
                ->shouldReceive('resolveRoute')
                ->twice()
                ->andReturn('route');

            $this->mockPageGeneratorService
                ->shouldReceive('replacePlaceholders')
                ->once()
                ->andReturnUsing(function (array $replacements) use ($name): string {
                    // All field types should be handled consistently
                    $formDefaults = $replacements['{{ formFieldsDefault }}'];

                    $this->assertIsString($formDefaults);

                    $this->assertStringContainsString($name, $formDefaults);

                    return 'content';
                });

            $this->mockPageGeneratorService
                ->shouldReceive('writeToFile')
                ->once();

            $generator->generate();
        }
    }

    public function test_generators_handle_route_resolution_failures_gracefully(): void
    {
        $config = new PageConfigDTO(
            model: 'TestModel',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
            ],
            pages: ['create'],
            routes: []
        );

        $generator = new CreatePageGenerator($config, $this->mockPageGeneratorService);

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->once()
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->once()
            ->andReturn('stub');

        // Simulate route resolution returning fallback routes
        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with(null, 'TestModel', 'store')
            ->once()
            ->andReturn('test-models.store');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with(null, 'TestModel', 'index')
            ->once()
            ->andReturn('test-models.index');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                $this->assertEquals('test-models.store', $replacements['{{ routeStore }}']);
                $this->assertEquals('test-models.index', $replacements['{{ routeIndex }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator->generate();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
