<?php

namespace App\Support;

use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;

class TestExecutionManager
{
    private array $runningProcesses = [];
    private array $completedTests = [];
    private array $failedTests = [];
    private float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Execute tests synchronously
     */
    public function executeSync(array $testCommands): array
    {
        $results = [];

        foreach ($testCommands as $name => $command) {
            echo "Executing: {$name}...\n";

            $result = FileLock::executeWithLock("test-{$name}", function () use ($command) {
                return $this->runCommand($command);
            });

            $results[$name] = $result;

            if ($result['exit_code'] !== 0) {
                $this->failedTests[] = $name;
                echo "✗ {$name} failed\n";
            } else {
                $this->completedTests[] = $name;
                echo "✓ {$name} passed\n";
            }
        }

        return $results;
    }

    /**
     * Execute tests in parallel with synchronization
     */
    public function executeParallel(array $testCommands, int $maxConcurrent = 4): array
    {
        $results = [];
        $pending = $testCommands;

        while (!empty($pending) || !empty($this->runningProcesses)) {
            // Start new processes up to maxConcurrent
            while (count($this->runningProcesses) < $maxConcurrent && !empty($pending)) {
                $name = array_key_first($pending);
                $command = array_shift($pending);

                $this->startProcess($name, $command);
            }

            // Check running processes
            foreach ($this->runningProcesses as $name => $process) {
                if (!$process->isRunning()) {
                    $results[$name] = [
                        'exit_code' => $process->getExitCode(),
                        'output' => $process->getOutput(),
                        'error' => $process->getErrorOutput(),
                    ];

                    if ($process->getExitCode() !== 0) {
                        $this->failedTests[] = $name;
                        echo "✗ {$name} failed\n";
                    } else {
                        $this->completedTests[] = $name;
                        echo "✓ {$name} passed\n";
                    }

                    unset($this->runningProcesses[$name]);
                }
            }

            usleep(100000); // Wait 100ms before next check
        }

        return $results;
    }

    /**
     * Wait for all processes to complete
     */
    public function waitForCompletion(int $timeout = 600): bool
    {
        $startTime = time();

        while (!empty($this->runningProcesses)) {
            if (time() - $startTime >= $timeout) {
                $this->killAllProcesses();
                return false;
            }

            foreach ($this->runningProcesses as $name => $process) {
                if (!$process->isRunning()) {
                    if ($process->getExitCode() === 0) {
                        $this->completedTests[] = $name;
                    } else {
                        $this->failedTests[] = $name;
                    }

                    unset($this->runningProcesses[$name]);
                }
            }

            usleep(500000); // Wait 500ms
        }

        return true;
    }

    /**
     * Get execution summary
     */
    public function getSummary(): array
    {
        $duration = microtime(true) - $this->startTime;

        return [
            'total' => count($this->completedTests) + count($this->failedTests),
            'passed' => count($this->completedTests),
            'failed' => count($this->failedTests),
            'duration' => round($duration, 2),
            'completed_tests' => $this->completedTests,
            'failed_tests' => $this->failedTests,
        ];
    }

    /**
     * Check if all tests passed
     */
    public function allTestsPassed(): bool
    {
        return empty($this->failedTests);
    }

    private function startProcess(string $name, string $command): void
    {
        echo "Starting: {$name}...\n";

        $process = SymfonyProcess::fromShellCommandline($command);
        $process->setTimeout(600);
        $process->start();

        $this->runningProcesses[$name] = $process;
    }

    private function runCommand(string $command): array
    {
        $process = SymfonyProcess::fromShellCommandline($command);
        $process->setTimeout(600);
        $process->run();

        return [
            'exit_code' => $process->getExitCode(),
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput(),
        ];
    }

    private function killAllProcesses(): void
    {
        foreach ($this->runningProcesses as $process) {
            if ($process->isRunning()) {
                $process->stop(10);
            }
        }

        $this->runningProcesses = [];
    }
}
