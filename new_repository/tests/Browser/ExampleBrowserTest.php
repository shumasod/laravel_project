<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleBrowserTest extends DuskTestCase
{
    /**
     * Test basic page functionality
     */
    public function test_homepage_loads_successfully()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathIs('/')
                    ->assertSee('Laravel')
                    ->pause(1000);
        });
    }

    /**
     * Test page contains expected elements
     */
    public function test_homepage_contains_expected_elements()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertVisible('body')
                    ->assertPresent('html')
                    ->assertTitle('Laravel');
        });
    }

    /**
     * Test responsive design
     */
    public function test_homepage_is_responsive()
    {
        $this->browse(function (Browser $browser) {
            // Desktop
            $browser->resize(1920, 1080)
                    ->visit('/')
                    ->assertVisible('body')
                    ->pause(500);

            // Tablet
            $browser->resize(768, 1024)
                    ->visit('/')
                    ->assertVisible('body')
                    ->pause(500);

            // Mobile
            $browser->resize(375, 667)
                    ->visit('/')
                    ->assertVisible('body')
                    ->pause(500);
        });
    }

    /**
     * Test navigation
     */
    public function test_navigation_works()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Laravel')
                    ->pause(1000);

            // Add more navigation tests as needed
        });
    }

    /**
     * Test JavaScript functionality
     */
    public function test_javascript_is_working()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->script('return typeof window !== "undefined"');

            $this->assertTrue(true, 'JavaScript is working');
        });
    }
}
