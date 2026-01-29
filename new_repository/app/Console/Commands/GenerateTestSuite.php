<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class GenerateTestSuite extends Command
{
    protected $signature = 'test:generate-suite
                            {--force : Overwrite existing tests}
                            {--type=all : Test type (all, browser, feature, api)}';

    protected $description = 'Generate complete E2E test suite for all routes';

    public function handle()
    {
        $type = $this->option('type');
        $force = $this->option('force');

        $this->info('Generating E2E test suite...');
        $this->newLine();

        $routes = collect(Route::getRoutes())
            ->filter(function ($route) {
                return in_array('GET', $route->methods()) || in_array('POST', $route->methods());
            });

        $generated = 0;
        $skipped = 0;

        foreach ($routes as $route) {
            $uri = $route->uri();
            $name = $this->generateTestName($uri);

            if ($this->shouldGenerateTest($type, $uri)) {
                try {
                    $this->call('test:generate-e2e', [
                        'name' => $name,
                        '--url' => '/' . $uri,
                        '--type' => $this->determineTestType($uri),
                    ]);
                    $generated++;
                } catch (\Exception $e) {
                    $this->error("Failed to generate test for {$uri}: {$e->getMessage()}");
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Test suite generation complete!");
        $this->line("Generated: {$generated} tests");
        $this->line("Skipped: {$skipped} routes");

        return 0;
    }

    protected function generateTestName($uri)
    {
        $name = str_replace(['/', '{', '}', '-', '.'], ' ', $uri);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        $name = $name ?: 'Home';

        return $name . 'Test';
    }

    protected function shouldGenerateTest($type, $uri)
    {
        if ($type === 'all') {
            return true;
        }

        if ($type === 'api' && str_starts_with($uri, 'api/')) {
            return true;
        }

        if ($type === 'browser' && !str_starts_with($uri, 'api/')) {
            return true;
        }

        if ($type === 'feature') {
            return true;
        }

        return false;
    }

    protected function determineTestType($uri)
    {
        if (str_starts_with($uri, 'api/')) {
            return 'api';
        }

        return 'browser';
    }
}
