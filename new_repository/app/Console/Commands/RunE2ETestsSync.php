<?php

namespace App\Console\Commands;

use App\Support\FileLock;
use App\Support\TestExecutionManager;
use Illuminate\Console\Command;

class RunE2ETestsSync extends Command
{
    protected $signature = 'test:run-e2e-sync
                            {--suite=all : Test suite to run (all, unit, feature, browser)}
                            {--parallel : Run tests in parallel}
                            {--max-concurrent=4 : Maximum concurrent tests in parallel mode}
                            {--timeout=600 : Test execution timeout in seconds}
                            {--report : Generate report after execution}';

    protected $description = 'Run E2E tests with synchronization management';

    public function handle()
    {
        $suite = $this->option('suite');
        $parallel = $this->option('parallel');
        $maxConcurrent = (int) $this->option('max-concurrent');
        $timeout = (int) $this->option('timeout');
        $shouldReport = $this->option('report');

        $this->info('Starting E2E test execution with synchronization...');
        $this->newLine();

        try {
            // Execute tests with lock to prevent concurrent executions
            $results = FileLock::executeWithLock('test-execution', function () use ($suite, $parallel, $maxConcurrent) {
                return $this->executeTests($suite, $parallel, $maxConcurrent);
            }, $timeout);

            // Display summary
            $this->displaySummary($results);

            // Generate report if requested
            if ($shouldReport) {
                $this->newLine();
                $this->info('Generating test report...');
                $this->call('test:generate-report', [
                    '--format' => 'html',
                    '--wait' => true,
                ]);
            }

            return $results['manager']->allTestsPassed() ? 0 : 1;
        } catch (\Exception $e) {
            $this->error("Test execution failed: {$e->getMessage()}");
            return 1;
        }
    }

    protected function executeTests(string $suite, bool $parallel, int $maxConcurrent): array
    {
        $manager = new TestExecutionManager();

        $testCommands = $this->getTestCommands($suite);

        $this->info("Executing {$suite} tests...");
        $this->newLine();

        if ($parallel) {
            $this->line("Running tests in parallel (max {$maxConcurrent} concurrent)");
            $results = $manager->executeParallel($testCommands, $maxConcurrent);
        } else {
            $this->line("Running tests synchronously");
            $results = $manager->executeSync($testCommands);
        }

        // Wait for all tests to complete
        $manager->waitForCompletion();

        return [
            'results' => $results,
            'manager' => $manager,
        ];
    }

    protected function getTestCommands(string $suite): array
    {
        $basePath = base_path();
        $commands = [];

        switch ($suite) {
            case 'unit':
                $commands['Unit Tests'] = "cd {$basePath} && vendor/bin/phpunit --testsuite Unit";
                break;

            case 'feature':
                $commands['Feature Tests'] = "cd {$basePath} && vendor/bin/phpunit --testsuite Feature";
                break;

            case 'browser':
                if (is_dir(base_path('tests/Browser'))) {
                    $commands['Browser Tests'] = "cd {$basePath} && php artisan dusk";
                }
                break;

            case 'all':
            default:
                $commands['Unit Tests'] = "cd {$basePath} && vendor/bin/phpunit --testsuite Unit";
                $commands['Feature Tests'] = "cd {$basePath} && vendor/bin/phpunit --testsuite Feature";

                if (is_dir(base_path('tests/Browser'))) {
                    $commands['Browser Tests'] = "cd {$basePath} && php artisan dusk";
                }
                break;
        }

        return $commands;
    }

    protected function displaySummary(array $results): void
    {
        $manager = $results['manager'];
        $summary = $manager->getSummary();

        $this->newLine();
        $this->line('═══════════════════════════════════════');
        $this->info('Test Execution Summary');
        $this->line('═══════════════════════════════════════');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Tests', $summary['total']],
                ['Passed', "<fg=green>{$summary['passed']}</>"],
                ['Failed', $summary['failed'] > 0 ? "<fg=red>{$summary['failed']}</>" : $summary['failed']],
                ['Duration', $summary['duration'] . 's'],
            ]
        );

        if (!empty($summary['failed_tests'])) {
            $this->newLine();
            $this->error('Failed Tests:');
            foreach ($summary['failed_tests'] as $test) {
                $this->line("  ✗ {$test}");
            }
        }

        if (!empty($summary['completed_tests'])) {
            $this->newLine();
            $this->info('Passed Tests:');
            foreach ($summary['completed_tests'] as $test) {
                $this->line("  ✓ {$test}");
            }
        }

        $this->newLine();
        $this->line('═══════════════════════════════════════');

        if ($manager->allTestsPassed()) {
            $this->info('✓ All tests passed!');
        } else {
            $this->error('✗ Some tests failed!');
        }
    }
}
