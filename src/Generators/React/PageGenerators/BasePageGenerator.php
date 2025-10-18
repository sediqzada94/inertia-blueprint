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

abstract class BasePageGenerator implements PageGeneratorInterface
{
    protected string $pageName;

    public function __construct(
        protected readonly PageConfigDTO $config,
        protected readonly PageGeneratorService $pageGenerator
    ) {}

    public function generate(): void
    {
        $outputPath = $this->pageGenerator->getOutputPath($this->config->model, $this->pageName);
        $stub = $this->replacedStubPlaceholders();
        $this->pageGenerator->writeToFile($outputPath, $stub);
    }

    /**
     * @param  Collection<int, FieldInterface>  $fields
     * @return array<string, string>
     */
    abstract protected function getReplacements(Collection $fields): array;

    protected function replacedStubPlaceholders(): string
    {
        $fields = $this->createFields();
        $replacements = $this->getReplacements($fields);

        /** @var array<string, string> $replacements */
        return $this->pageGenerator->replacePlaceholders(
            $replacements,
            $this->pageGenerator->readStub($this->pageName)
        );
    }

    /**
     * @return Collection<int, FieldInterface>
     */
    protected function createFields(): Collection
    {
        $context = $this->getFieldContext();

        return collect($this->config->fields)
            ->map(fn (FieldConfigDTO $field): FieldInterface => FieldFactory::create($field, $context));
    }

    protected function getFieldContext(): string
    {
        return strtolower($this->pageName);
    }

    protected function getTypeScriptType(string $type): string
    {
        return match ($type) {
            'string', 'text', 'email', 'password' => 'string',
            'integer', 'number' => 'number',
            'boolean' => 'boolean',
            'datetime', 'date' => 'string',
            default => 'string'
        };
    }

    protected function indentContent(string $content, int $spaces): string
    {
        $trimmedContent = Str::of($content)->trim();

        if ($trimmedContent->isEmpty()) {
            return '';
        }

        $indent = Str::of(' ')->repeat($spaces);
        $lines = $trimmedContent->explode("\n");

        return $indent.$lines->implode("\n".$indent);
    }

    /**
     * @param  Collection<int, FieldInterface>  $fields
     */
    protected function getSelectTypes(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field): string => $field->getTypeDefinition())
            ->filter()
            ->implode(PHP_EOL.PHP_EOL);
    }

    /**
     * @param  Collection<int, FieldInterface>  $fields
     */
    protected function getPropsTypes(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field): string => $field->getPropTypeDeclaration())
            ->filter()
            ->implode(PHP_EOL);
    }

    /**
     * @param  Collection<int, FieldInterface>  $fields
     */
    protected function getStaticOptions(Collection $fields): string
    {
        return $fields
            ->map(fn (FieldInterface $field): string => $field->getFieldOption())
            ->filter()
            ->implode(PHP_EOL);
    }

    protected function resolveRoute(?string $route, string $action): string
    {
        return $this->pageGenerator->resolveRoute(
            $route,
            $this->config->model,
            $action
        );
    }

    /**
     * @param  Collection<int, FieldInterface>  $fields
     */
    protected function getFormInputs(Collection $fields): string
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

    protected function getModelCamel(): string
    {
        return Str::of($this->config->model)->camel()->toString();
    }

    protected function getModelPluralCamel(): string
    {
        return Str::of($this->config->model)->camel()->plural()->toString();
    }

    protected function getModelLower(): string
    {
        return Str::of($this->config->model)->lower()->toString();
    }
}
