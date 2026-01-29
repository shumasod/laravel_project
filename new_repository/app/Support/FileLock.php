<?php

namespace App\Support;

use RuntimeException;

class FileLock
{
    private $lockFile;
    private $lockHandle;
    private bool $isLocked = false;

    public function __construct(string $lockFile)
    {
        $this->lockFile = $lockFile;
        $this->ensureLockDirectory();
    }

    /**
     * Acquire an exclusive lock
     */
    public function acquire(int $timeout = 30): bool
    {
        $this->lockHandle = fopen($this->lockFile, 'c');

        if (!$this->lockHandle) {
            throw new RuntimeException("Cannot open lock file: {$this->lockFile}");
        }

        $startTime = time();

        while (true) {
            if (flock($this->lockHandle, LOCK_EX | LOCK_NB)) {
                $this->isLocked = true;
                $this->writeLockInfo();
                return true;
            }

            if (time() - $startTime >= $timeout) {
                fclose($this->lockHandle);
                return false;
            }

            usleep(100000); // Wait 100ms before retry
        }
    }

    /**
     * Release the lock
     */
    public function release(): void
    {
        if ($this->isLocked && $this->lockHandle) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
            $this->isLocked = false;
            @unlink($this->lockFile);
        }
    }

    /**
     * Execute a callback with lock
     */
    public static function executeWithLock(string $lockName, callable $callback, int $timeout = 30)
    {
        $lock = new self(storage_path("locks/{$lockName}.lock"));

        try {
            if (!$lock->acquire($timeout)) {
                throw new RuntimeException("Could not acquire lock: {$lockName}");
            }

            return $callback();
        } finally {
            $lock->release();
        }
    }

    /**
     * Check if a lock exists
     */
    public static function exists(string $lockName): bool
    {
        $lockFile = storage_path("locks/{$lockName}.lock");
        return file_exists($lockFile);
    }

    /**
     * Wait for a lock to be released
     */
    public static function waitForRelease(string $lockName, int $timeout = 60): bool
    {
        $lockFile = storage_path("locks/{$lockName}.lock");
        $startTime = time();

        while (file_exists($lockFile)) {
            if (time() - $startTime >= $timeout) {
                return false;
            }

            usleep(500000); // Wait 500ms
        }

        return true;
    }

    private function ensureLockDirectory(): void
    {
        $directory = dirname($this->lockFile);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function writeLockInfo(): void
    {
        fwrite($this->lockHandle, json_encode([
            'pid' => getmypid(),
            'timestamp' => time(),
            'host' => gethostname(),
        ]));
        fflush($this->lockHandle);
    }

    public function __destruct()
    {
        $this->release();
    }
}
