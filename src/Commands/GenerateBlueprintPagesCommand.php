<?php

namespace Sediqzada\InertiaBlueprint\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Sediqzada\InertiaBlueprint\DTOs\PageConfigDTO;
use Sediqzada\InertiaBlueprint\Generators\Services\PageGeneratorService;
use Sediqzada\InertiaBlueprint\Services\ConfigLoaderService;
use Sediqzada\InertiaBlueprint\Support\PageGeneratorResolver;

class GenerateBlueprintPagesCommand extends Command
{
    protected $signature = 'blueprint:generate {file?}';

    protected $description = 'Generate Inertia.js pages from a JSON blueprint';

    public function __construct(
        private readonly ConfigLoaderService $configLoader,
        private readonly PageGeneratorService $pageGenerator
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $fileArg = $this->argument('file');

            $path = base_path(is_string($fileArg) ? $fileArg : 'blueprint.json');

            $config = $this->configLoader->load($path);

            $result = $this->generatePages($config);

            $this->displaySuccessMessage($config, $result);

            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }
    }

    /**
     * @return array{
     *   existingPages: list<string>,
     *   generatedPages: list<string>,
     *   userChoice: ('ignore'|'override')|null
     * }
     */
    private function generatePages(PageConfigDTO $config): array
    {
        /** @var list<string> $existingPages */
        $existingPages = [];
        /** @var list<string> $pagesToGenerate */
        $pagesToGenerate = [];
        /** @var ('ignore'|'override')|null $userChoice */
        $userChoice = null;

        // Check which pages already exist
        foreach ($config->pages as $page) {
            $generator = PageGeneratorResolver::resolve(
                $page,
                $config,
                $this->pageGenerator
            );

            $pagePath = $this->getPagePath($page, $config);

            if (File::exists($pagePath)) {
                $existingPages[] = $page;
            } else {
                $pagesToGenerate[] = $page;
            }
        }

        // If there are existing pages, ask user what to do
        if ($existingPages !== []) {
            $this->warn('The following pages already exist:');
            foreach ($existingPages as $page) {
                $this->line('  - '.Str::of($page)->ucfirst());
            }

            $raw = $this->choice(
                'What would you like to do with existing pages?',
                ['ignore', 'override'],
                'override'
            );

            // Normalize to our exact union
            $userChoice = in_array($raw, ['ignore', 'override'], true) ? $raw : 'override';
            /** @var 'ignore'|'override' $userChoice */
            if ($userChoice === 'ignore') {
                $this->info('Skipping existing pages. Only generating new pages.');
            } else {
                $this->info('Overriding existing pages.');
                $pagesToGenerate = array_merge($pagesToGenerate, $existingPages);
            }
        } else {
            $pagesToGenerate = $config->pages;
        }

        // Generate the pages
        foreach ($pagesToGenerate as $page) {
            $generator = PageGeneratorResolver::resolve(
                $page,
                $config,
                $this->pageGenerator
            );
            $generator->generate();
        }

        return [
            'existingPages' => $existingPages,
            'generatedPages' => $pagesToGenerate,
            'userChoice' => $userChoice,
        ];
    }

    private function getPagePath(string $page, PageConfigDTO $config): string
    {
        $modelName = $config->model;
        $pageName = Str::of($page)->ucfirst();

        return resource_path("js/pages/{$modelName}/{$pageName}.tsx");
    }

    /**
     * @param array{
     *   existingPages: list<string>,
     *   generatedPages: list<string>,
     *   userChoice: ('ignore'|'override')|null
     * } $result
     */
    private function displaySuccessMessage(PageConfigDTO $config, array $result): void
    {
        $existingPages = $result['existingPages'];
        $generatedPages = $result['generatedPages'];
        $userChoice = $result['userChoice'];

        if (empty($generatedPages)) {
            $this->info('✅ No pages were generated (all existing pages were ignored).');

            return;
        }

        $pages = collect($generatedPages)
            ->map(fn ($item) => Str::of($item)->ucfirst())
            ->pipe(function ($collection) {
                if ($collection->count() === 1) {
                    return $collection->first();
                }

                return $collection->slice(0, -1)->implode(', ')
                    .' and '
                    .$collection->last();
            });

        $action = 'generated';
        if (! empty($existingPages) && $userChoice === 'override') {
            $action = 'generated (overridden)';
        } elseif (! empty($existingPages) && $userChoice === 'ignore') {
            $action = 'generated (existing pages ignored)';
        }

        $this->info("✅ {$pages} Pages {$action} for model: {$config->model}");
    }
}
