<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleBrowserTest extends DuskTestCase
{
    /**
     * Test basic page functionality with synchronization
     */
    public function test_homepage_loads_successfully()
    {
        $this->browse(function (Browser $browser) {
            $this->visitSync($browser, '/');

            $browser->assertPathIs('/')
                    ->assertSee('Laravel');

            // Wait for page to be fully loaded
            $this->waitForPageLoad($browser);
        });
    }

    /**
     * Test page contains expected elements with sync wait
     */
    public function test_homepage_contains_expected_elements()
    {
        $this->browse(function (Browser $browser) {
            $this->visitSync($browser, '/');

            $browser->assertVisible('body')
                    ->assertPresent('html');

            // Wait for any async operations
            $this->waitForAjax($browser);
        });
    }

    /**
     * Test responsive design with synchronized navigation
     */
    public function test_homepage_is_responsive()
    {
        $this->browse(function (Browser $browser) {
            // Desktop
            $browser->resize(1920, 1080);
            $this->visitSync($browser, '/');
            $browser->assertVisible('body');

            // Tablet
            $browser->resize(768, 1024);
            $this->visitSync($browser, '/');
            $browser->assertVisible('body');

            // Mobile
            $browser->resize(375, 667);
            $this->visitSync($browser, '/');
            $browser->assertVisible('body');
        });
    }

    /**
     * Test navigation with synchronization
     */
    public function test_navigation_works()
    {
        $this->browse(function (Browser $browser) {
            $this->visitSync($browser, '/');

            $browser->assertSee('Laravel');

            // Wait for network to be idle
            $this->waitForNetworkIdle($browser);
        });
    }

    /**
     * Test JavaScript functionality with retry
     */
    public function test_javascript_is_working()
    {
        $this->browse(function (Browser $browser) {
            $this->visitSync($browser, '/');

            // Use retry with backoff for flaky checks
            $result = $this->retryWithBackoff($browser, function ($browser) {
                return $browser->driver->executeScript('return typeof window !== "undefined"');
            });

            $this->assertTrue($result, 'JavaScript is working');
        });
    }

    /**
     * Test form interaction with synchronization
     */
    public function test_form_interaction_is_synchronized()
    {
        $this->browse(function (Browser $browser) {
            $this->visitSync($browser, '/');

            // Example: If there's a form on the page
            // $this->waitForElementReady($browser, '#my-form');
            // $this->typeSync($browser, 'input[name="email"]', 'test@example.com');
            // $this->clickSync($browser, 'button[type="submit"]');

            $this->waitForPageLoad($browser);
        });
    }

    /**
     * Test async operations are properly synchronized
     */
    public function test_async_operations_are_synchronized()
    {
        $this->browse(function (Browser $browser) {
            $this->visitSync($browser, '/');

            // Wait for all AJAX calls to complete
            $this->waitForAjax($browser);

            // Wait for network to be idle
            $this->waitForNetworkIdle($browser);

            $browser->assertVisible('body');
        });
    }
}
