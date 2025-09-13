<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\PageGenerators;

use Mockery;
use Mockery\MockInterface;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\ViewPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class ViewPageGeneratorTest extends TestCase
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
                    name: 'is_active',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
            ],
            pages: ['view'],
            routes: [
                'edit' => 'users.edit',
                'index' => 'users.index',
            ]
        );
    }

    public function test_implements_page_generator_interface(): void
    {
        $generator = new ViewPageGenerator($this->pageConfig, $this->mockPageGeneratorService);

        $this->assertInstanceOf(\Sediqzada\InertiaBlueprint\Contracts\PageGeneratorInterface::class, $generator);
    }

    public function test_generate_calls_required_service_methods(): void
    {
        $stubContent = 'stub content with {{ model }} placeholder';
        $outputPath = 'resources/js/Pages/User/View.tsx';

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->with('User', 'View')
            ->once()
            ->andReturn($outputPath);

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->with('View')
            ->once()
            ->andReturn($stubContent);

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('users.edit', 'User', 'edit')
            ->once()
            ->andReturn('users.edit');

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
                $this->assertArrayHasKey('{{ modelCamel }}', $replacements);
                $this->assertArrayHasKey('{{ fields }}', $replacements);
                $this->assertArrayHasKey('{{ viewFields }}', $replacements);
                $this->assertArrayHasKey('{{ routeEdit }}', $replacements);
                $this->assertArrayHasKey('{{ routeIndex }}', $replacements);

                $this->assertEquals('User', $replacements['{{ model }}']);
                $this->assertEquals('user', $replacements['{{ modelCamel }}']);
                $this->assertEquals('users.edit', $replacements['{{ routeEdit }}']);
                $this->assertEquals('users.index', $replacements['{{ routeIndex }}']);

                return 'processed content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->with($outputPath, 'processed content')
            ->once();

        $generator = new ViewPageGenerator($this->pageConfig, $this->mockPageGeneratorService);
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
            ],
            pages: ['view'],
            routes: []
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->with('Document', 'View')
            ->once()
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->with('View')
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
                // File fields should generate a link in view fields

                $this->assertIsString($replacements['{{ viewFields }}']);

                $this->assertStringContainsString('document.attachment ? <a href={document.attachment}', $replacements['{{ viewFields }}']);
                $this->assertStringContainsString('View File', $replacements['{{ viewFields }}']);
                $this->assertStringContainsString('No file', $replacements['{{ viewFields }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new ViewPageGenerator($configWithFileField, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_textarea_fields(): void
    {
        $configWithTextareaFields = new PageConfigDTO(
            model: 'Article',
            fields: [
                new FieldConfigDTO(
                    name: 'title',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'content',
                    type: 'text',
                    inputType: 'textarea'
                ),
                new FieldConfigDTO(
                    name: 'summary',
                    type: 'text',
                    inputType: 'textarea'
                ),
            ],
            pages: ['view'],
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
            ->twice()
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Textarea fields should be rendered with special styling
                $viewFields = $replacements['{{ viewFields }}'];

                $this->assertIsString($viewFields);

                $this->assertStringContainsString('bg-gray-50 p-4 rounded-md', $viewFields);
                $this->assertStringContainsString('article.content', $viewFields);
                $this->assertStringContainsString('article.summary', $viewFields);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new ViewPageGenerator($configWithTextareaFields, $this->mockPageGeneratorService);
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
            pages: ['view'],
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
            ->twice()
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Array-based select fields should use field values directly
                $viewFields = $replacements['{{ viewFields }}'];

                $this->assertIsString($viewFields);

                $this->assertStringContainsString('product.status', $viewFields);
                $this->assertStringContainsString('product.priority_level', $viewFields);

                // Should not generate select types for array options
                $selectTypes = $replacements['{{ selectTypes }}'];
                $this->assertEquals('', $selectTypes);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new ViewPageGenerator($configWithSelectArrayOptions, $this->mockPageGeneratorService);
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
            pages: ['view'],
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
            ->twice()
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                // Relationship-based select fields should use relationship objects
                $viewFields = $replacements['{{ viewFields }}'];

                $this->assertIsString($viewFields);

                $this->assertStringContainsString('post.category?.name', $viewFields);
                $this->assertStringContainsString('post.author?.email', $viewFields);

                // Should generate select types for relationships
                $selectTypes = $replacements['{{ selectTypes }}'];
                $this->assertNotEmpty($selectTypes);

                // Should include both field name and relationship in fields
                $fields = $replacements['{{ fields }}'];

                $this->assertIsString($fields);

                $this->assertStringContainsString('category_id: number', $fields);
                $this->assertStringContainsString('category: Category', $fields);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new ViewPageGenerator($configWithSelectRelationships, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_mixed_field_layout(): void
    {
        $configWithMixedLayout = new PageConfigDTO(
            model: 'Event',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'location',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'is_public',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
                new FieldConfigDTO(
                    name: 'description',
                    type: 'text',
                    inputType: 'textarea'
                ),
                new FieldConfigDTO(
                    name: 'notes',
                    type: 'text',
                    inputType: 'textarea'
                ),
            ],
            pages: ['view'],
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
            ->twice()
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                $viewFields = $replacements['{{ viewFields }}'];

                $this->assertIsString($viewFields);

                // Should separate grid fields from textarea fields
                $this->assertStringContainsString('</div>', $viewFields);

                // Grid fields should be in regular divs
                $this->assertStringContainsString('event.name', $viewFields);
                $this->assertStringContainsString('event.location', $viewFields);

                // Boolean fields should use CircleCheck/CircleX
                $this->assertStringContainsString('event.is_public ? <CircleCheck', $viewFields);

                // Textarea fields should have special styling
                $this->assertStringContainsString('bg-gray-50 p-4 rounded-md', $viewFields);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new ViewPageGenerator($configWithMixedLayout, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_complex_field_names_in_labels(): void
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
                    name: 'date_of_birth',
                    type: 'date',
                    inputType: 'date'
                ),
                new FieldConfigDTO(
                    name: 'is_email_verified',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
                new FieldConfigDTO(
                    name: 'bio_description',
                    type: 'text',
                    inputType: 'textarea'
                ),
            ],
            pages: ['view'],
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
            ->twice()
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                $viewFields = $replacements['{{ viewFields }}'];

                $this->assertIsString($viewFields);

                // Should convert field names to proper titles in labels
                $this->assertStringContainsString('First Name', $viewFields);
                $this->assertStringContainsString('Date Of Birth', $viewFields);
                $this->assertStringContainsString('Is Email Verified', $viewFields);
                $this->assertStringContainsString('Bio Description', $viewFields);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new ViewPageGenerator($configWithComplexFieldNames, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_only_grid_fields(): void
    {
        $configWithOnlyGridFields = new PageConfigDTO(
            model: 'SimpleModel',
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
                    name: 'is_active',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
            ],
            pages: ['view'],
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
            ->twice()
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                $viewFields = $replacements['{{ viewFields }}'];

                $this->assertIsString($viewFields);

                // Should end with closing div for grid fields only
                $this->assertStringContainsString('</div>', $viewFields);

                // Should not have textarea styling
                $this->assertStringNotContainsString('bg-gray-50 p-4 rounded-md', $viewFields);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new ViewPageGenerator($configWithOnlyGridFields, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_only_textarea_fields(): void
    {
        $configWithOnlyTextareaFields = new PageConfigDTO(
            model: 'Article',
            fields: [
                new FieldConfigDTO(
                    name: 'content',
                    type: 'text',
                    inputType: 'textarea'
                ),
                new FieldConfigDTO(
                    name: 'summary',
                    type: 'text',
                    inputType: 'textarea'
                ),
            ],
            pages: ['view'],
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
            ->twice()
            ->andReturn('route');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                $viewFields = $replacements['{{ viewFields }}'];

                $this->assertIsString($viewFields);

                // Should have textarea styling for all fields
                $this->assertStringContainsString('bg-gray-50 p-4 rounded-md', $viewFields);

                // Should still have proper structure
                $this->assertStringContainsString('</div>', $viewFields);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new ViewPageGenerator($configWithOnlyTextareaFields, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_handles_model_camel_case(): void
    {
        $testCases = [
            ['User', 'user'],
            ['BlogPost', 'blogPost'],
            ['UserProfile', 'userProfile'],
            ['ProductCategory', 'productCategory'],
        ];

        foreach ($testCases as [$modelName, $expectedCamelCase]) {
            $config = new PageConfigDTO(
                model: $modelName,
                fields: [
                    new FieldConfigDTO(
                        name: 'name',
                        type: 'string',
                        inputType: 'text'
                    ),
                ],
                pages: ['view'],
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
                ->twice()
                ->andReturn('route');

            $this->mockPageGeneratorService
                ->shouldReceive('replacePlaceholders')
                ->once()
                ->andReturnUsing(function (array $replacements) use ($expectedCamelCase): string {
                    $this->assertEquals($expectedCamelCase, $replacements['{{ modelCamel }}']);

                    // Should use camel case in view fields
                    $viewFields = $replacements['{{ viewFields }}'];

                    $this->assertIsString($viewFields);

                    $this->assertStringContainsString("{$expectedCamelCase}.name", $viewFields);

                    return 'content';
                });

            $this->mockPageGeneratorService
                ->shouldReceive('writeToFile')
                ->once();

            $generator = new ViewPageGenerator($config, $this->mockPageGeneratorService);
            $generator->generate();
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
