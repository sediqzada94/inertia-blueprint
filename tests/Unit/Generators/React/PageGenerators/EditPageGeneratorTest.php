<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\PageGenerators;

use Mockery;
use Mockery\MockInterface;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\EditPageGenerator;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class EditPageGeneratorTest extends TestCase
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
            ],
            pages: ['edit'],
            routes: [
                'update' => 'users.update',
                'index' => 'users.index',
            ]
        );
    }

    public function test_implements_page_generator_interface(): void
    {
        $generator = new EditPageGenerator($this->pageConfig, $this->mockPageGeneratorService);

        $this->assertInstanceOf(\Sediqzada\InertiaBlueprint\Contracts\PageGeneratorInterface::class, $generator);
    }

    public function test_generate_calls_required_service_methods(): void
    {
        $stubContent = 'stub content with {{ model }} placeholder';
        $outputPath = 'resources/js/Pages/User/Edit.tsx';

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->with('User', 'Edit')
            ->once()
            ->andReturn($outputPath);

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->with('Edit')
            ->once()
            ->andReturn($stubContent);

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('users.update', 'User', 'update')
            ->once()
            ->andReturn('users.update');

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
                $this->assertArrayHasKey('{{ formFieldsDefault }}', $replacements);
                $this->assertArrayHasKey('{{ routeUpdate }}', $replacements);
                $this->assertArrayHasKey('{{ routeIndex }}', $replacements);

                $this->assertEquals('User', $replacements['{{ model }}']);
                $this->assertEquals('user', $replacements['{{ modelCamel }}']);
                $this->assertEquals('users.update', $replacements['{{ routeUpdate }}']);
                $this->assertEquals('users.index', $replacements['{{ routeIndex }}']);

                return 'processed content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->with($outputPath, 'processed content')
            ->once();

        $generator = new EditPageGenerator($this->pageConfig, $this->mockPageGeneratorService);
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
            pages: ['edit'],
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
                // File fields should have "string | File" type in TypeScript

                $this->assertIsString($replacements['{{ fields }}']);

                $this->assertStringContainsString('attachment: string | File', $replacements['{{ fields }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new EditPageGenerator($configWithFileField, $this->mockPageGeneratorService);
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
                new FieldConfigDTO(
                    name: 'is_verified',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
            ],
            pages: ['edit'],
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
                // Boolean fields should have boolean type in TypeScript

                $this->assertIsString($replacements['{{ fields }}']);
                $this->assertIsString($replacements['{{ formFieldsDefault }}']);

                $this->assertStringContainsString('is_active: boolean', $replacements['{{ fields }}']);
                $this->assertStringContainsString('is_verified: boolean', $replacements['{{ fields }}']);

                // Form defaults should reference model properties
                $this->assertStringContainsString('is_active: user.is_active', $replacements['{{ formFieldsDefault }}']);
                $this->assertStringContainsString('is_verified: user.is_verified', $replacements['{{ formFieldsDefault }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new EditPageGenerator($configWithBooleanField, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_select_fields_relationship(): void
    {
        $configWithSelectField = new PageConfigDTO(
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
            pages: ['edit'],
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
                // Should include relationship data in inputs
                $inputs = $replacements['{{ inputs }}'];

                $this->assertIsString($inputs);

                $this->assertStringContainsString('post, categories, authors', $inputs);

                // Should have proper type definitions
                $selectTypes = $replacements['{{ selectTypes }}'];
                $this->assertNotEmpty($selectTypes);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new EditPageGenerator($configWithSelectField, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_datetime_fields(): void
    {
        $configWithDatetimeFields = new PageConfigDTO(
            model: 'Event',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'start_date',
                    type: 'datetime',
                    inputType: 'datetime-local'
                ),
                new FieldConfigDTO(
                    name: 'end_date',
                    type: 'date',
                    inputType: 'date'
                ),
            ],
            pages: ['edit'],
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
                // Date/datetime fields should have string type in TypeScript

                $this->assertIsString($replacements['{{ fields }}']);

                $this->assertStringContainsString('start_date: string', $replacements['{{ fields }}']);
                $this->assertStringContainsString('end_date: string', $replacements['{{ fields }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new EditPageGenerator($configWithDatetimeFields, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_number_fields(): void
    {
        $configWithNumberFields = new PageConfigDTO(
            model: 'Product',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'price',
                    type: 'number',
                    inputType: 'number'
                ),
                new FieldConfigDTO(
                    name: 'quantity',
                    type: 'integer',
                    inputType: 'number'
                ),
            ],
            pages: ['edit'],
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
                // Number fields should have number type in TypeScript

                $this->assertIsString($replacements['{{ fields }}']);

                $this->assertStringContainsString('price: number', $replacements['{{ fields }}']);
                $this->assertStringContainsString('quantity: number', $replacements['{{ fields }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new EditPageGenerator($configWithNumberFields, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_mixed_field_layout(): void
    {
        $configWithMixedLayout = new PageConfigDTO(
            model: 'Article',
            fields: [
                new FieldConfigDTO(
                    name: 'title',
                    type: 'string',
                    inputType: 'text'
                ),
                new FieldConfigDTO(
                    name: 'slug',
                    type: 'string',
                    inputType: 'text'
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
                    name: 'is_published',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
            ],
            pages: ['edit'],
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
                // Should separate grid fields from textarea fields
                $formInputs = $replacements['{{ formInputs }}'];

                $this->assertIsString($formInputs);

                $this->assertStringContainsString('</div>', $formInputs);

                // Should have proper model camel case
                $this->assertEquals('article', $replacements['{{ modelCamel }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new EditPageGenerator($configWithMixedLayout, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_custom_field_names_and_relationships(): void
    {
        $configWithCustomNames = new PageConfigDTO(
            model: 'Order',
            fields: [
                new FieldConfigDTO(
                    name: 'customer',
                    type: 'integer',
                    inputType: 'select',
                    fieldName: 'customer_id',
                    options: 'customers',
                    valueField: 'id',
                    labelField: 'full_name'
                ),
                new FieldConfigDTO(
                    name: 'shipping_address',
                    type: 'text',
                    inputType: 'textarea',
                    fieldName: 'shipping_address'
                ),
            ],
            pages: ['edit'],
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
                // Should use custom field names in form defaults
                $formDefaults = $replacements['{{ formFieldsDefault }}'];

                $this->assertIsString($formDefaults);

                $this->assertStringContainsString('customer_id: order.customer_id', $formDefaults);
                $this->assertStringContainsString('shipping_address: order.shipping_address', $formDefaults);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new EditPageGenerator($configWithCustomNames, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_no_select_fields(): void
    {
        $configWithoutSelects = new PageConfigDTO(
            model: 'SimpleModel',
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
            ],
            pages: ['edit'],
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
                // Should handle case with no select fields
                $inputs = $replacements['{{ inputs }}'];
                $this->assertEquals('simpleModel', $inputs);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new EditPageGenerator($configWithoutSelects, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_handles_complex_model_names_camel_case(): void
    {
        $testCases = [
            ['BlogPost', 'blogPost'],
            ['UserProfile', 'userProfile'],
            ['ProductCategory', 'productCategory'],
            ['APIKey', 'aPIKey'],
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
                pages: ['edit'],
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

                    return 'content';
                });

            $this->mockPageGeneratorService
                ->shouldReceive('writeToFile')
                ->once();

            $generator = new EditPageGenerator($config, $this->mockPageGeneratorService);
            $generator->generate();
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
