<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\PageGenerators;

use Mockery;
use Mockery\MockInterface;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\IndexPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class IndexPageGeneratorTest extends TestCase
{
    private PageGeneratorService&MockInterface $mockPageGeneratorService;

    private PageConfigDTO $pageConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockPageGeneratorService = Mockery::mock(PageGeneratorService::class);

        $this->pageConfig = new PageConfigDTO(
            model: 'User',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'email',
                    type: 'string',
                    inputType: 'email'
                ),
                new FieldConfigDTO(
                    name: 'active',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
            ],
            pages: ['index'],
            routes: [
                'create' => 'users.create',
                'edit' => 'users.edit',
                'show' => 'users.show',
                'destroy' => 'users.destroy',
                'index' => 'users.index',
            ]
        );
    }

    public function test_implements_page_generator_interface(): void
    {
        $generator = new IndexPageGenerator($this->pageConfig, $this->mockPageGeneratorService);

        $this->assertInstanceOf(\Sediqzada\InertiaBlueprint\Contracts\PageGeneratorInterface::class, $generator);
    }

    public function test_generate_calls_required_service_methods(): void
    {
        $stubContent = 'stub content with {{ model }} placeholder';
        $outputPath = 'resources/js/Pages/User/Index.tsx';

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->with('User', 'Index')
            ->once()
            ->andReturn($outputPath);

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->with('Index')
            ->once()
            ->andReturn($stubContent);

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('users.create', 'User', 'create')
            ->once()
            ->andReturn('users.create');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('users.edit', 'User', 'edit')
            ->once()
            ->andReturn('users.edit');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('users.show', 'User', 'show')
            ->once()
            ->andReturn('users.show');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('users.destroy', 'User', 'destroy')
            ->once()
            ->andReturn('users.destroy');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('users.index', 'User', 'index')
            ->once()
            ->andReturn('users.index');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements, $stub): string {
                $this->assertArrayHasKey('{{ model }}', $replacements);
                $this->assertArrayHasKey('{{ modelPluralCamel }}', $replacements);
                $this->assertArrayHasKey('{{ modelLower }}', $replacements);
                $this->assertArrayHasKey('{{ fields }}', $replacements);
                $this->assertArrayHasKey('{{ tableHeaders }}', $replacements);
                $this->assertArrayHasKey('{{ tableCells }}', $replacements);

                $this->assertEquals('User', $replacements['{{ model }}']);
                $this->assertEquals('users', $replacements['{{ modelPluralCamel }}']);
                $this->assertEquals('user', $replacements['{{ modelLower }}']);

                return 'processed content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->with($outputPath, 'processed content')
            ->once();

        $generator = new IndexPageGenerator($this->pageConfig, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_boolean_fields(): void
    {
        $configWithBooleanField = new PageConfigDTO(
            model: 'User',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'is_active',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
            ],
            pages: ['index'],
            routes: []
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->times(5)
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Boolean fields should use CircleCheck/CircleX icons

                $this->assertIsString($replacements['{{ tableCells }}']);

                $this->assertStringContainsString('item.is_active ? <CircleCheck', $replacements['{{ tableCells }}']);
                $this->assertStringContainsString('<CircleX', $replacements['{{ tableCells }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new IndexPageGenerator($configWithBooleanField, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_file_fields(): void
    {
        $configWithFileField = new PageConfigDTO(
            model: 'Document',
            fields: [
                new FieldConfigDTO(
                    name: 'title',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'attachment',
                    type: 'string',
                    inputType: 'file'
                ),
                new FieldConfigDTO(
                    name: 'thumbnail',
                    type: 'string',
                    inputType: 'file'
                ),
            ],
            pages: ['index'],
            routes: []
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->times(5)
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // File fields should generate links in table cells
                $tableCells = $replacements['{{ tableCells }}'];

                $this->assertIsString($tableCells);

                $this->assertStringContainsString('item.attachment ? <a href={item.attachment}', $tableCells);
                $this->assertStringContainsString('View File', $tableCells);
                $this->assertStringContainsString('No file', $tableCells);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new IndexPageGenerator($configWithFileField, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_select_fields_array_options(): void
    {
        $configWithSelectArrayOptions = new PageConfigDTO(
            model: 'Product',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'status',
                    type: 'string',
                    inputType: 'select',
                    options: ['active', 'inactive', 'pending']
                ),
                new FieldConfigDTO(
                    name: 'priority',
                    type: 'string',
                    inputType: 'select',
                    fieldName: 'priority_level',
                    options: ['low', 'medium', 'high']
                ),
            ],
            pages: ['index'],
            routes: []
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->times(5)
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Array-based select fields should use field values directly
                $tableCells = $replacements['{{ tableCells }}'];

                $this->assertIsString($tableCells);

                $this->assertStringContainsString('item.status', $tableCells);
                $this->assertStringContainsString('item.priority_level', $tableCells);

                // Should not generate select types for array options
                $selectTypes = $replacements['{{ selectTypes }}'];
                $this->assertEquals('', $selectTypes);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new IndexPageGenerator($configWithSelectArrayOptions, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_select_fields_relationship_options(): void
    {
        $configWithSelectRelationships = new PageConfigDTO(
            model: 'Post',
            fields: [
                new FieldConfigDTO(
                    name: 'title',
                    type: 'string',
                    inputType: 'text'
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
                    name: 'author',
                    type: 'integer',
                    inputType: 'select',
                    fieldName: 'author_id',
                    options: 'users',
                    valueField: 'id',
                    labelField: 'email'
                ),
            ],
            pages: ['index'],
            routes: []
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->times(5)
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Relationship-based select fields should use relationship objects
                $tableCells = $replacements['{{ tableCells }}'];

                $this->assertIsString($tableCells);

                $this->assertStringContainsString('item.category?.name', $tableCells);
                $this->assertStringContainsString('item.author?.email', $tableCells);

                // Should generate select types for relationships
                $selectTypes = $replacements['{{ selectTypes }}'];
                $this->assertNotEmpty($selectTypes);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new IndexPageGenerator($configWithSelectRelationships, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_searchable_fields(): void
    {
        $configWithSearchableFields = new PageConfigDTO(
            model: 'User',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text',
                    searchable: true
                ),
                new FieldConfigDTO(
                    name: 'email',
                    type: 'string',
                    inputType: 'email',
                    searchable: true
                ),
                new FieldConfigDTO(
                    name: 'phone',
                    type: 'string',
                    inputType: 'text',
                    searchable: false
                ),
            ],
            pages: ['index'],
            routes: []
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->times(5)
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Should indicate searchable fields are present
                $this->assertTrue((bool) $replacements['{{ hasSearchableFields }}']);

                // Should generate appropriate search placeholder
                $searchPlaceholder = $replacements['{{ searchPlaceholder }}'];

                $this->assertIsString($searchPlaceholder);

                $this->assertStringContainsString('Search by Name, Email', $searchPlaceholder);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new IndexPageGenerator($configWithSearchableFields, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_no_searchable_fields(): void
    {
        $configWithoutSearchableFields = new PageConfigDTO(
            model: 'Product',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text',
                    searchable: false
                ),
                new FieldConfigDTO(
                    name: 'price',
                    type: 'number',
                    inputType: 'number',
                    searchable: false
                ),
            ],
            pages: ['index'],
            routes: []
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->times(5)
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Should indicate no searchable fields
                $this->assertEquals('false', $replacements['{{ hasSearchableFields }}']);

                // Should generate default search placeholder
                $searchPlaceholder = $replacements['{{ searchPlaceholder }}'];
                $this->assertEquals('Search Products...', $searchPlaceholder);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new IndexPageGenerator($configWithoutSearchableFields, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_complex_field_names_in_headers(): void
    {
        $configWithComplexFieldNames = new PageConfigDTO(
            model: 'UserProfile',
            fields: [
                new FieldConfigDTO(
                    name: 'first_name',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'last_name',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'date_of_birth',
                    type: 'date',
                    inputType: 'date'
                ),
                new FieldConfigDTO(
                    name: 'is_email_verified',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
            ],
            pages: ['index'],
            routes: []
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->times(5)
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Should convert field names to proper titles in headers
                $tableHeaders = $replacements['{{ tableHeaders }}'];

                $this->assertIsString($tableHeaders);

                $this->assertStringContainsString('First Name', $tableHeaders);
                $this->assertStringContainsString('Last Name', $tableHeaders);
                $this->assertStringContainsString('Date Of Birth', $tableHeaders);
                $this->assertStringContainsString('Is Email Verified', $tableHeaders);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new IndexPageGenerator($configWithComplexFieldNames, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_handles_model_pluralization(): void
    {
        $testCases = [
            ['User', 'users', 'user'],
            ['Category', 'categories', 'category'],
            ['BlogPost', 'blogPosts', 'blogpost'],
            ['Company', 'companies', 'company'],
        ];

        foreach ($testCases as [$modelName, $expectedPlural, $expectedLower]) {
            $config = new PageConfigDTO(
                model: $modelName,
                fields: [
                    new FieldConfigDTO(
                        name: 'name',
                        type: 'string',
                        inputType: 'text'
                    ),
                ],
                pages: ['index'],
                routes: []
            );

            $this->mockPageGeneratorService
                ->shouldReceive('getOutputPath')
                ->andReturn('path');

            $this->mockPageGeneratorService
                ->shouldReceive('readStub')
                ->andReturn('stub');

            $this->mockPageGeneratorService
                ->shouldReceive('resolveRoute')
                ->times(5)
                ->andReturn('route');

            $this->mockPageGeneratorService
                ->shouldReceive('replacePlaceholders')
                ->once()
                ->andReturnUsing(function (array $replacements) use ($expectedPlural, $expectedLower): string {
                    $this->assertEquals($expectedPlural, $replacements['{{ modelPluralCamel }}']);
                    $this->assertEquals($expectedLower, $replacements['{{ modelLower }}']);

                    return 'content';
                });

            $this->mockPageGeneratorService
                ->shouldReceive('writeToFile')
                ->once();

            $generator = new IndexPageGenerator($config, $this->mockPageGeneratorService);
            $generator->generate();
        }
    }

    public function test_generate_with_all_field_types_mixed(): void
    {
        $configWithAllFieldTypes = new PageConfigDTO(
            model: 'ComplexModel',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'description',
                    type: 'text',
                    inputType: 'textarea'
                ),
                new FieldConfigDTO(
                    name: 'is_active',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
                new FieldConfigDTO(
                    name: 'attachment',
                    type: 'string',
                    inputType: 'file'
                ),
                new FieldConfigDTO(
                    name: 'category',
                    type: 'integer',
                    inputType: 'select',
                    fieldName: 'category_id',
                    options: 'categories'
                ),
                new FieldConfigDTO(
                    name: 'status',
                    type: 'string',
                    inputType: 'select',
                    options: ['draft', 'published', 'archived']
                ),
            ],
            pages: ['index'],
            routes: []
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->times(5)
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                $tableCells = $replacements['{{ tableCells }}'];

                // Should handle all field types appropriately

                $this->assertIsString($tableCells);

                $this->assertStringContainsString('item.name', $tableCells);
                $this->assertStringContainsString('item.description', $tableCells);
                $this->assertStringContainsString('item.is_active ? <CircleCheck', $tableCells);
                $this->assertStringContainsString('item.attachment ? <a href={item.attachment}', $tableCells);
                $this->assertStringContainsString('item.category?.', $tableCells);
                $this->assertStringContainsString('item.status', $tableCells);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new IndexPageGenerator($configWithAllFieldTypes, $this->mockPageGeneratorService);
        $generator->generate();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
