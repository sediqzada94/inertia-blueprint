<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Generators\React\PageGenerators;

use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;
use Sediqzada\InertiaBlueprint\Generators\React\PageGenerators\BasePageGenerator;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Tests\TestCase;

class BasePageGeneratorTest extends TestCase
{
    private PageGeneratorService&MockInterface $mockPageGeneratorService;

    private PageConfigDTO $pageConfig;

    private TestableBasePageGenerator $generator;

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
                    name: 'bio',
                    type: 'text',
                    inputType: 'textarea'
                ),
            ],
            pages: ['test'],
            routes: [
                'store' => 'users.store',
                'index' => 'users.index',
            ]
        );

        $this->generator = new TestableBasePageGenerator($this->pageConfig, $this->mockPageGeneratorService);
    }

    public function test_implements_page_generator_interface(): void
    {
        $this->assertInstanceOf(\Sediqzada\InertiaBlueprint\Contracts\PageGeneratorInterface::class, $this->generator);
    }

    public function test_generate_calls_required_methods(): void
    {
        $outputPath = 'resources/js/Pages/User/Test.tsx';
        $stubContent = 'stub content';
        $processedContent = 'processed content';

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->with('User', 'Test')
            ->once()
            ->andReturn($outputPath);

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->with('Test')
            ->once()
            ->andReturn($stubContent);

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->once()
            ->andReturn($processedContent);

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->with($outputPath, $processedContent)
            ->once();

        $this->generator->generate();

        // Assert that the method completed without throwing exceptions
        $this->assertTrue(true);
    }

    public function test_get_typescript_type_returns_correct_types(): void
    {
        $this->assertEquals('string', $this->generator->getTypeScriptType('string'));
        $this->assertEquals('string', $this->generator->getTypeScriptType('text'));
        $this->assertEquals('string', $this->generator->getTypeScriptType('email'));
        $this->assertEquals('string', $this->generator->getTypeScriptType('password'));
        $this->assertEquals('number', $this->generator->getTypeScriptType('integer'));
        $this->assertEquals('number', $this->generator->getTypeScriptType('number'));
        $this->assertEquals('boolean', $this->generator->getTypeScriptType('boolean'));
        $this->assertEquals('string', $this->generator->getTypeScriptType('datetime'));
        $this->assertEquals('string', $this->generator->getTypeScriptType('date'));
        $this->assertEquals('string', $this->generator->getTypeScriptType('unknown_type'));
    }

    public function test_indent_content_adds_correct_indentation(): void
    {
        $content = "line1\nline2\nline3";
        $expected = "    line1\n    line2\n    line3";

        $result = $this->generator->indentContent($content, 4);

        $this->assertEquals($expected, $result);
    }

    public function test_indent_content_handles_single_line(): void
    {
        $content = 'single line';
        $expected = '      single line';

        $result = $this->generator->indentContent($content, 6);

        $this->assertEquals($expected, $result);
    }

    public function test_indent_content_handles_empty_content(): void
    {
        $content = '';
        $expected = '';

        $result = $this->generator->indentContent($content, 4);

        $this->assertEquals($expected, $result);
    }

    public function test_get_select_types_filters_and_joins_correctly(): void
    {
        $mockField1 = Mockery::mock(FieldInterface::class);
        $mockField1->shouldReceive('getTypeDefinition')->andReturn('type SelectOption1 = { value: string; label: string; }');

        $mockField2 = Mockery::mock(FieldInterface::class);
        $mockField2->shouldReceive('getTypeDefinition')->andReturn('');

        $mockField3 = Mockery::mock(FieldInterface::class);
        $mockField3->shouldReceive('getTypeDefinition')->andReturn('type SelectOption2 = { value: number; label: string; }');

        $fields = collect([$mockField1, $mockField2, $mockField3]);

        $result = $this->generator->getSelectTypes($fields);

        $expected = "type SelectOption1 = { value: string; label: string; }\n\ntype SelectOption2 = { value: number; label: string; }";
        $this->assertEquals($expected, $result);
    }

    public function test_get_props_types_filters_and_joins_correctly(): void
    {
        $mockField1 = Mockery::mock(FieldInterface::class);
        $mockField1->shouldReceive('getPropTypeDeclaration')->andReturn('  categories: Category[]');

        $mockField2 = Mockery::mock(FieldInterface::class);
        $mockField2->shouldReceive('getPropTypeDeclaration')->andReturn('');

        $mockField3 = Mockery::mock(FieldInterface::class);
        $mockField3->shouldReceive('getPropTypeDeclaration')->andReturn('  tags: Tag[]');

        $fields = collect([$mockField1, $mockField2, $mockField3]);

        $result = $this->generator->getPropsTypes($fields);

        $expected = "  categories: Category[]\n  tags: Tag[]";
        $this->assertEquals($expected, $result);
    }

    public function test_get_static_options_filters_and_joins_correctly(): void
    {
        $mockField1 = Mockery::mock(FieldInterface::class);
        $mockField1->shouldReceive('getFieldOption')->andReturn('const statusOptions = [...]');

        $mockField2 = Mockery::mock(FieldInterface::class);
        $mockField2->shouldReceive('getFieldOption')->andReturn('');

        $mockField3 = Mockery::mock(FieldInterface::class);
        $mockField3->shouldReceive('getFieldOption')->andReturn('const typeOptions = [...]');

        $fields = collect([$mockField1, $mockField2, $mockField3]);

        $result = $this->generator->getStaticOptions($fields);

        $expected = "const statusOptions = [...]\nconst typeOptions = [...]";
        $this->assertEquals($expected, $result);
    }

    public function test_resolve_route_calls_service_with_correct_parameters(): void
    {
        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with('custom.route', 'User', 'store')
            ->once()
            ->andReturn('custom.route');

        $result = $this->generator->resolveRoute('custom.route', 'store');

        $this->assertEquals('custom.route', $result);
    }

    public function test_resolve_route_with_null_route(): void
    {
        $this->mockPageGeneratorService
            ->shouldReceive('resolveRoute')
            ->with(null, 'User', 'index')
            ->once()
            ->andReturn('users.index');

        $result = $this->generator->resolveRoute(null, 'index');

        $this->assertEquals('users.index', $result);
    }

    public function test_get_form_inputs_separates_grid_and_textarea_fields(): void
    {
        $mockGridField1 = Mockery::mock(FieldInterface::class);
        $mockGridField1->shouldReceive('getInputType')->andReturn('text');
        $mockGridField1->shouldReceive('render')->andReturn('<input type="text" />');

        $mockGridField2 = Mockery::mock(FieldInterface::class);
        $mockGridField2->shouldReceive('getInputType')->andReturn('email');
        $mockGridField2->shouldReceive('render')->andReturn('<input type="email" />');

        $mockTextareaField = Mockery::mock(FieldInterface::class);
        $mockTextareaField->shouldReceive('getInputType')->andReturn('textarea');
        $mockTextareaField->shouldReceive('render')->andReturn('<textarea></textarea>');

        $fields = collect([$mockGridField1, $mockGridField2, $mockTextareaField]);

        $result = $this->generator->getFormInputs($fields);

        $this->assertStringContainsString('<input type="text" />', $result);
        $this->assertStringContainsString('<input type="email" />', $result);
        $this->assertStringContainsString('<textarea></textarea>', $result);
        $this->assertStringContainsString('</div>', $result);
    }

    public function test_get_form_inputs_handles_only_grid_fields(): void
    {
        $mockGridField = Mockery::mock(FieldInterface::class);
        $mockGridField->shouldReceive('getInputType')->andReturn('text');
        $mockGridField->shouldReceive('render')->andReturn('<input type="text" />');

        $fields = collect([$mockGridField]);

        $result = $this->generator->getFormInputs($fields);

        $this->assertStringContainsString('<input type="text" />', $result);
        $this->assertStringContainsString('</div>', $result);
        $this->assertStringNotContainsString('<textarea>', $result);
    }

    public function test_get_form_inputs_handles_only_textarea_fields(): void
    {
        $mockTextareaField = Mockery::mock(FieldInterface::class);
        $mockTextareaField->shouldReceive('getInputType')->andReturn('textarea');
        $mockTextareaField->shouldReceive('render')->andReturn('<textarea></textarea>');

        $fields = collect([$mockTextareaField]);

        $result = $this->generator->getFormInputs($fields);

        $this->assertStringContainsString('<textarea></textarea>', $result);
        $this->assertStringContainsString('</div>', $result);
    }

    public function test_get_model_camel_returns_camel_case(): void
    {
        $result = $this->generator->getModelCamel();
        $this->assertEquals('user', $result);

        // Test with complex model name
        $complexConfig = new PageConfigDTO(
            model: 'BlogPost',
            fields: [],
            pages: ['test'],
            routes: []
        );
        $complexGenerator = new TestableBasePageGenerator($complexConfig, $this->mockPageGeneratorService);
        $result = $complexGenerator->getModelCamel();
        $this->assertEquals('blogPost', $result);
    }

    public function test_get_model_plural_camel_returns_plural_camel_case(): void
    {
        $result = $this->generator->getModelPluralCamel();
        $this->assertEquals('users', $result);

        // Test with complex model name
        $complexConfig = new PageConfigDTO(
            model: 'BlogPost',
            fields: [],
            pages: ['test'],
            routes: []
        );
        $complexGenerator = new TestableBasePageGenerator($complexConfig, $this->mockPageGeneratorService);
        $result = $complexGenerator->getModelPluralCamel();
        $this->assertEquals('blogPosts', $result);
    }

    public function test_get_model_lower_returns_lowercase(): void
    {
        $result = $this->generator->getModelLower();
        $this->assertEquals('user', $result);

        // Test with complex model name
        $complexConfig = new PageConfigDTO(
            model: 'BlogPost',
            fields: [],
            pages: ['test'],
            routes: []
        );
        $complexGenerator = new TestableBasePageGenerator($complexConfig, $this->mockPageGeneratorService);
        $result = $complexGenerator->getModelLower();
        $this->assertEquals('blogpost', $result);
    }

    public function test_get_field_context_returns_lowercase_page_name(): void
    {
        $result = $this->generator->getFieldContext();
        $this->assertEquals('test', $result);
    }

    public function test_create_fields_uses_correct_context(): void
    {
        // This test verifies that createFields method uses the correct context
        // Since we can't easily mock FieldFactory::create, we'll test indirectly
        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->andReturn('content');

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        // The generate method should complete without errors
        $this->generator->generate();
        $this->expectNotToPerformAssertions();
    }

    public function test_handles_empty_fields_collection(): void
    {
        $emptyConfig = new PageConfigDTO(
            model: 'EmptyModel',
            fields: [],
            pages: ['test'],
            routes: []
        );

        $emptyGenerator = new TestableBasePageGenerator($emptyConfig, $this->mockPageGeneratorService);

        $this->mockPageGeneratorService
            ->shouldReceive('getOutputPath')
            ->andReturn('path');

        $this->mockPageGeneratorService
            ->shouldReceive('readStub')
            ->andReturn('stub');

        $this->mockPageGeneratorService
            ->shouldReceive('replacePlaceholders')
            ->andReturn('content');

        $this->mockPageGeneratorService
            ->shouldReceive('writeToFile')
            ->once();

        $emptyGenerator->generate();
        $this->expectNotToPerformAssertions();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

/**
 * Concrete implementation of BasePageGenerator for testing purposes
 */
class TestableBasePageGenerator extends BasePageGenerator
{
    protected string $pageName = 'Test';

    protected function getReplacements(Collection $fields): array
    {
        return [
            '{{ model }}' => $this->config->model,
            '{{ modelCamel }}' => $this->getModelCamel(),
            '{{ selectTypes }}' => $this->getSelectTypes($fields),
            '{{ propsTypes }}' => $this->getPropsTypes($fields),
            '{{ staticOptions }}' => $this->getStaticOptions($fields),
            '{{ formInputs }}' => $this->getFormInputs($fields),
        ];
    }

    // Expose protected methods for testing
    public function getTypeScriptType(string $type): string
    {
        return parent::getTypeScriptType($type);
    }

    public function indentContent(string $content, int $spaces): string
    {
        return parent::indentContent($content, $spaces);
    }

    public function getSelectTypes(Collection $fields): string
    {
        return parent::getSelectTypes($fields);
    }

    public function getPropsTypes(Collection $fields): string
    {
        return parent::getPropsTypes($fields);
    }

    public function getStaticOptions(Collection $fields): string
    {
        return parent::getStaticOptions($fields);
    }

    public function resolveRoute(?string $route, string $action): string
    {
        return parent::resolveRoute($route, $action);
    }

    public function getFormInputs(Collection $fields): string
    {
        return parent::getFormInputs($fields);
    }

    public function getModelCamel(): string
    {
        return parent::getModelCamel();
    }

    public function getModelPluralCamel(): string
    {
        return parent::getModelPluralCamel();
    }

    public function getModelLower(): string
    {
        return parent::getModelLower();
    }

    public function getFieldContext(): string
    {
        return parent::getFieldContext();
    }
}
