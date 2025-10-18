<?php

namespace Sediqzada\InertiaBlueprint\Generators\React\PageGenerators;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sediqzada\InertiaBlueprint\Contracts\PageGeneratorInterface;
use Sediqzada\InertiaBlueprint\DTOs\FieldConfigDTO;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldFactory;
use Sediqzada\InertiaBlueprint\Generators\React\Fields\FieldInterface;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;

class CreatePageGenerator implements PageGeneratorInterface
{
    private string $pageName = 'Create';

    public function __construct(
        private readonly PageConfigDTO $config,
        private readonly PageGeneratorService $pageGenerator
    ) {}

    public function generate(): void
    {
        $outputPath = $this->pageGenerator->getOutputPath($this->config->model, $this->pageName);
        $stub = $this->replacedStubPlaceholders();
        $this->pageGenerator->writeToFile($outputPath, $stub);
    }

    private function replacedStubPlaceholders(): string
    {
        $fields = $this->createFields();

        $replacements = [
            '{{ formFieldsDefault }}' => $this->getFormFieldsDefault($fields),
            '{{ routeStore }}' => $this->pageGenerator->resolveRoute(
                $this->config->routes['store'] ?? null,
                $this->config->model,
                'store'
            ),
            '{{ routeIndex }}' => $this->pageGenerator->resolveRoute(
                $this->config->routes['index'] ?? null,
                $this->config->model,
                'index'
            ),
            '{{ model }}' => $this->config->model,
            '{{ formInputs }}' => $this->getFormInputs($fields),
            '{{ selectTypes }}' => $this->getSelectTypes($fields),
            '{{ selectInputs }}' => $this->getSelectInputs($fields),
            '{{ propsTypes }}' => $this->getPropsTypes($fields),
            '{{ selectFieldStaticOptions }}' => $this->getStaticOptions($fields),
        ];

        return $this->pageGenerator->replacePlaceholders(
            $replacements,
            $this->pageGenerator->readStub($this->pageName)
        );
    }

    /**
     * @return Collection<int, FieldInterface>
     */
    private function createFields(): Collection
    {
        return collect($this->config->fields)
            ->map(fn (FieldConfigDTO $field): FieldInterface => FieldFactory::create($field, 'create'));
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getFormFieldsDefault(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field) => Str::of($field->getDefaultValueDeclaration())->prepend('    '))
            ->implode(PHP_EOL);
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getFormInputs(Collection $fields): string
    {
        $gridFields = $fields->filter(fn (FieldInterface $field): bool => $field->getInputType() !== 'textarea');
        $textareaFields = $fields->filter(fn (FieldInterface $field): bool => $field->getInputType() === 'textarea');

        $gridFieldsHtml = $gridFields
            ->map(fn (FieldInterface $field): string => $this->indentContent($field->render(), 12))
            ->implode(PHP_EOL);

        $textareaFieldsHtml = $textareaFields
            ->map(fn (FieldInterface $field): string => $this->indentContent($field->render(), 10))
            ->implode(PHP_EOL);

        return $gridFieldsHtml.($textareaFieldsHtml ? PHP_EOL.'          </div>'.PHP_EOL.$textareaFieldsHtml : PHP_EOL.'          </div>');
    }

    private function indentContent(string $content, int $spaces): string
    {
        $indent = Str::of(' ')->repeat($spaces);
        $lines = Str::of($content)->trim()->explode("\n");

        return $indent.$lines->implode("\n".$indent);
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getSelectTypes(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field): string => $field->getTypeDefinition())
            ->filter()
            ->implode(PHP_EOL.PHP_EOL);
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getSelectInputs(Collection $fields): string
    {
        $selectInputs = $fields
            ->map(fn (FieldInterface $field): string => $field->getInputDeclaration())
            ->filter()
            ->implode(', ');

        return $selectInputs ?: '';
    }

    /**
     * @param  FieldCollection  $fields
     */
    private function getPropsTypes(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field): string => $field->getPropTypeDeclaration())
            ->filter()
            ->implode(PHP_EOL);
    }

    private function getStaticOptions(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field): string => $field->getFieldOption())
            ->filter()
            ->implode(PHP_EOL);
    }
}
