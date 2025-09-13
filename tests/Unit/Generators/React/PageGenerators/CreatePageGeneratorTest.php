<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\PageGenerators;

use Mockery;
use Mockery\MockInterface;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\CreatePageGenerator;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class CreatePageGeneratorTest extends TestCase
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
            pages: ['create'],
            routes: [
                'store' => 'users.store',
                'index' => 'users.index',
            ]
        );
    }

    public function test_implements_page_generator_interface(): void
    {
        $generator = new CreatePageGenerator($this->pageConfig, $this->mockPageGeneratorService);

        $this->assertInstanceOf(\Sediqzada\InertiaBlueprint\Contracts\PageGeneratorInterface::class, $generator);
    }

    public function test_generate_calls_required_service_methods(): void
    {
        $stubContent = 'stub content with {{ model }} placeholder';
        $outputPath = 'resources/js/Pages/User/Create.tsx';

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->with('User', 'Create')
            ->once()
            ->andReturn($outputPath);

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->with('Create')
            ->once()
            ->andReturn($stubContent);

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('users.store', 'User', 'store')
            ->once()
            ->andReturn('users.store');

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
                $this->assertArrayHasKey('{{ formFieldsDefault }}', $replacements);
                $this->assertArrayHasKey('{{ routeStore }}', $replacements);
                $this->assertArrayHasKey('{{ routeIndex }}', $replacements);
                $this->assertArrayHasKey('{{ formInputs }}', $replacements);

                $this->assertEquals('User', $replacements['{{ model }}']);
                $this->assertEquals('users.store', $replacements['{{ routeStore }}']);
                $this->assertEquals('users.index', $replacements['{{ routeIndex }}']);

                return 'processed content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->with($outputPath, 'processed content')
            ->once();

        $generator = new CreatePageGenerator($this->pageConfig, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_null_routes_uses_defaults(): void
    {
        $configWithNullRoutes = new PageConfigDTO(
            model: 'Article',
            fields: [
                new FieldConfigDTO(
                    name: 'title',
                    type: 'string',
                    inputType: 'text'
                ),
            ],
            pages: ['create'],
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
            ->with(null, 'Article', 'store')
            ->once()
            ->andReturn('articles.store');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with(null, 'Article', 'index')
            ->once()
            ->andReturn('articles.index');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturn('content');

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new CreatePageGenerator($configWithNullRoutes, $this->mockPageGeneratorService);
        $generator->generate();

        $this->expectNotToPerformAssertions();
    }

    public function test_generate_with_select_fields_array_options(): void
    {
        $configWithSelectField = new PageConfigDTO(
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
                    options: [
                        ['id' => 'active', 'name' => 'Active'],
                        ['id' => 'inactive', 'name' => 'Inactive'],
                        ['id' => 'pending', 'name' => 'Pending'],
                    ],
                    valueField: 'id',
                    labelField: 'name'
                ),
            ],
            pages: ['create'],
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
                // Should contain select types and inputs for array options
                $this->assertArrayHasKey('{{ selectTypes }}', $replacements);
                $this->assertArrayHasKey('{{ selectInputs }}', $replacements);
                $this->assertArrayHasKey('{{ propsTypes }}', $replacements);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new CreatePageGenerator($configWithSelectField, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_select_fields_relationship_options(): void
    {
        $configWithRelationshipSelect = new PageConfigDTO(
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
            ],
            pages: ['create'],
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
                // Should handle relationship-based select fields
                $this->assertArrayHasKey('{{ selectInputs }}', $replacements);

                $this->assertIsString($replacements['{{ selectInputs }}']);

                $this->assertStringContainsString('categories', $replacements['{{ selectInputs }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new CreatePageGenerator($configWithRelationshipSelect, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_textarea_fields(): void
    {
        $configWithTextarea = new PageConfigDTO(
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
            pages: ['create'],
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
                // Textarea fields should be handled separately in form inputs
                $formInputs = $replacements['{{ formInputs }}'];

                $this->assertIsString($formInputs);

                $this->assertStringContainsString('</div>', $formInputs);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new CreatePageGenerator($configWithTextarea, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_mixed_field_types(): void
    {
        $configWithMixedFields = new PageConfigDTO(
            model: 'Event',
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
                    name: 'is_public',
                    type: 'boolean',
                    inputType: 'checkbox'
                ),
                new FieldConfigDTO(
                    name: 'category',
                    type: 'string',
                    inputType: 'select',
                    options: [
                        ['id' => 'conference', 'name' => 'Conference'],
                        ['id' => 'workshop', 'name' => 'Workshop'],
                        ['id' => 'meetup', 'name' => 'Meetup'],
                    ],
                    valueField: 'id',
                    labelField: 'name'
                ),
                new FieldConfigDTO(
                    name: 'banner',
                    type: 'string',
                    inputType: 'file'
                ),
            ],
            pages: ['create'],
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
                // Should handle all field types appropriately
                $this->assertArrayHasKey('{{ formFieldsDefault }}', $replacements);
                $this->assertArrayHasKey('{{ formInputs }}', $replacements);
                $this->assertArrayHasKey('{{ selectTypes }}', $replacements);
                $this->assertArrayHasKey('{{ selectInputs }}', $replacements);
                $this->assertArrayHasKey('{{ propsTypes }}', $replacements);

                // Form inputs should separate grid fields from textarea fields
                $formInputs = $replacements['{{ formInputs }}'];

                $this->assertIsString($formInputs);

                $this->assertStringContainsString('</div>', $formInputs);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new CreatePageGenerator($configWithMixedFields, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_custom_field_names(): void
    {
        $configWithCustomFieldNames = new PageConfigDTO(
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
                    name: 'product',
                    type: 'integer',
                    inputType: 'select',
                    fieldName: 'product_id',
                    options: 'products'
                ),
            ],
            pages: ['create'],
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

                $this->assertStringContainsString('customer_id', $formDefaults);
                $this->assertStringContainsString('product_id', $formDefaults);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new CreatePageGenerator($configWithCustomFieldNames, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_with_empty_fields_array(): void
    {
        $configWithNoFields = new PageConfigDTO(
            model: 'EmptyModel',
            fields: [],
            pages: ['create'],
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
                // Should handle empty fields gracefully
                $this->assertEquals('', $replacements['{{ formFieldsDefault }}']);

                $this->assertIsString($replacements['{{ formInputs }}']);

                $this->assertStringContainsString('</div>', $replacements['{{ formInputs }}']);
                $this->assertEquals('', $replacements['{{ selectTypes }}']);
                $this->assertEquals('', $replacements['{{ selectInputs }}']);
                $this->assertEquals('', $replacements['{{ propsTypes }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new CreatePageGenerator($configWithNoFields, $this->mockPageGeneratorService);
        $generator->generate();
    }

    public function test_generate_handles_complex_model_names(): void
    {
        $complexModels = ['BlogPost', 'UserProfile', 'ProductCategory'];

        foreach ($complexModels as $modelName) {
            $config = new PageConfigDTO(
                model: $modelName,
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

            $this->mockPageGeneratorService
                ->shouldReceive('getOutputPath')
                ->with($modelName, 'Create')
                ->once()
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
                ->andReturnUsing(function (array $replacements) use ($modelName): string {
                    $this->assertEquals($modelName, $replacements['{{ model }}']);

                    return 'content';
                });

            $this->mockPageGeneratorService
                ->shouldReceive('writeToFile')
                ->once();

            $generator = new CreatePageGenerator($config, $this->mockPageGeneratorService);
            $generator->generate();
        }
    }

    public function test_generate_with_all_route_types(): void
    {
        $configWithAllRoutes = new PageConfigDTO(
            model: 'Product',
            fields: [
                new FieldConfigDTO(
                    name: 'name',
                    type: 'string',
                    inputType: 'text'
                ),
            ],
            pages: ['create'],
            routes: [
                'store' => 'admin.products.store',
                'index' => 'admin.products.index',
                'create' => 'admin.products.create',
                'edit' => 'admin.products.edit',
                'show' => 'admin.products.show',
                'destroy' => 'admin.products.destroy',
            ]
        );

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('admin.products.store', 'Product', 'store')
            ->once()
            ->andReturn('admin.products.store');

        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('admin.products.index', 'Product', 'index')
            ->once()
            ->andReturn('admin.products.index');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturnUsing(function (array $replacements): string {
                $this->assertEquals('admin.products.store', $replacements['{{ routeStore }}']);
                $this->assertEquals('admin.products.index', $replacements['{{ routeIndex }}']);

                return 'content';
            });

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $generator = new CreatePageGenerator($configWithAllRoutes, $this->mockPageGeneratorService);
        $generator->generate();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
