<?php

namespace Sediqzada\InertiaBlueprint\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishStubsCommand extends Command
{
    protected $signature = 'blueprint:publish-stubs {--force : Overwrite existing stubs}';

    protected $description = 'Publish Inertia Blueprint stub files for customization';

    public function handle(): int
    {
        $stubsPath = __DIR__.'/../../stubs';
        $publishPath = resource_path('inertia-blueprint-stubs');

        if (File::exists($publishPath) && ! $this->option('force') && ! $this->confirm('Stubs already exist. Do you want to overwrite them?')) {
            $this->info('Publishing cancelled.');

            return 0;
        }

        try {
            // Ensure the target directory exists
            File::ensureDirectoryExists($publishPath);

            // Copy all stub files
            File::copyDirectory($stubsPath, $publishPath);

            $this->info('✅ Inertia Blueprint stubs published successfully!');
            $this->line('');
            $this->line('Stubs published to: <comment>'.$publishPath.'</comment>');
            $this->line('You can now customize the stub files to fit your needs.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to publish stubs: '.$e->getMessage());

            return 1;
        }
    }
}
