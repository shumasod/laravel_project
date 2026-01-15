<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Default wait timeout in seconds
     */
    protected int $waitTimeout = 30;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     */
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance with enhanced synchronization.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
                '--no-sandbox',
                '--disable-dev-shm-usage',
            ]);
        })->all());

        $driver = RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );

        // Set implicit wait timeout for better synchronization
        $driver->manage()->timeouts()->implicitlyWait($this->waitTimeout);

        return $driver;
    }

    /**
     * Wait for page to be fully loaded
     */
    protected function waitForPageLoad(Browser $browser, int $timeout = null): Browser
    {
        $timeout = $timeout ?? $this->waitTimeout;

        return $browser->waitUsing($timeout, 100, function () use ($browser) {
            return $browser->driver->executeScript('return document.readyState') === 'complete';
        });
    }

    /**
     * Wait for all AJAX requests to complete
     */
    protected function waitForAjax(Browser $browser, int $timeout = null): Browser
    {
        $timeout = $timeout ?? $this->waitTimeout;

        return $browser->waitUsing($timeout, 100, function () use ($browser) {
            $hasJQuery = $browser->driver->executeScript('return typeof jQuery !== "undefined"');

            if ($hasJQuery) {
                return $browser->driver->executeScript('return jQuery.active') === 0;
            }

            return true;
        });
    }

    /**
     * Wait for element to be visible and ready
     */
    protected function waitForElementReady(Browser $browser, string $selector, int $timeout = null): Browser
    {
        $timeout = $timeout ?? $this->waitTimeout;

        return $browser->waitFor($selector, $timeout)
                      ->waitUntilEnabled($selector, $timeout);
    }

    /**
     * Synchronously visit a page and wait for it to load
     */
    protected function visitSync(Browser $browser, string $url): Browser
    {
        $browser->visit($url);
        $this->waitForPageLoad($browser);
        $this->waitForAjax($browser);

        return $browser;
    }

    /**
     * Synchronously click an element and wait for response
     */
    protected function clickSync(Browser $browser, string $selector): Browser
    {
        $browser->click($selector);
        $this->waitForAjax($browser);
        $this->waitForPageLoad($browser);

        return $browser;
    }

    /**
     * Type into input with synchronization
     */
    protected function typeSync(Browser $browser, string $selector, string $value): Browser
    {
        $this->waitForElementReady($browser, $selector);
        $browser->type($selector, $value);

        // Wait a bit for any onChange handlers
        $browser->pause(100);

        return $browser;
    }

    /**
     * Wait for network idle
     */
    protected function waitForNetworkIdle(Browser $browser, int $timeout = null): Browser
    {
        $timeout = $timeout ?? $this->waitTimeout;

        $browser->driver->wait($timeout)->until(
            WebDriverExpectedCondition::jsCondition(
                'return window.performance && ' .
                'window.performance.getEntriesByType("resource")' .
                '.filter(r => !r.responseEnd).length === 0'
            )
        );

        return $browser;
    }

    /**
     * Retry an action with exponential backoff
     */
    protected function retryWithBackoff(Browser $browser, callable $action, int $maxAttempts = 3, int $initialDelay = 100): mixed
    {
        $attempt = 0;
        $delay = $initialDelay;

        while ($attempt < $maxAttempts) {
            try {
                return $action($browser);
            } catch (\Throwable $e) {
                $attempt++;

                if ($attempt >= $maxAttempts) {
                    throw $e;
                }

                $browser->pause($delay);
                $delay *= 2; // Exponential backoff
            }
        }

        return null;
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Determine if the browser window should start maximized.
     */
    protected function shouldStartMaximized(): bool
    {
        return isset($_SERVER['DUSK_START_MAXIMIZED']) ||
               isset($_ENV['DUSK_START_MAXIMIZED']);
    }
}
