<?php

namespace Sediqzada\InertiaBlueprint\DTOs;

class PageConfigDTO
{
    /**
     * @param  FieldConfigDTO[]  $fields
     * @param  list<string>  $pages
     * @param  array<string, string>|null  $routes
     */
    public function __construct(
        public readonly string $model,
        public readonly array $fields,
        public readonly array $pages,
        public readonly ?array $routes = null
    ) {}

    /**
     * @param array{
     *   model: string,
     *   fields: list<array{
     *     name: string,
     *     type: string,
     *     inputType: string,
     *     field_name?: string|null,
     *     options?: array<mixed>|null,
     *     valueField?: string|null,
     *     labelField?: string|null,
     *     searchable?: bool
     *   }>,
     *   pages: list<string>,
     *   routes?: array<string, string>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            model: $data['model'],
            fields: array_map(
                fn (array $field): FieldConfigDTO => FieldConfigDTO::fromArray($field),
                $data['fields']
            ),
            pages: $data['pages'],
            routes: $data['routes'] ?? null
        );
    }
}
