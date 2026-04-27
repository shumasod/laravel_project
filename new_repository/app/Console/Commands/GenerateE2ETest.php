<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateE2ETest extends Command
{
    protected $signature = 'test:generate-e2e
                            {name : The name of the test class}
                            {--url= : The URL to test}
                            {--type=browser : The type of test (browser, api, feature)}';

    protected $description = 'Generate E2E test code automatically';

    public function handle()
    {
        $name = $this->argument('name');
        $url = $this->option('url') ?? '/';
        $type = $this->option('type');

        $testContent = match($type) {
            'browser' => $this->generateBrowserTest($name, $url),
            'api' => $this->generateApiTest($name, $url),
            'feature' => $this->generateFeatureTest($name, $url),
            default => $this->generateBrowserTest($name, $url),
        };

        $directory = match($type) {
            'browser' => base_path('tests/Browser'),
            'api' => base_path('tests/Feature/Api'),
            'feature' => base_path('tests/Feature'),
            default => base_path('tests/Browser'),
        };

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = "{$directory}/{$name}.php";
        File::put($filename, $testContent);

        $this->info("E2E Test generated successfully: {$filename}");
        $this->line("Test type: {$type}");
        $this->line("Test URL: {$url}");

        return 0;
    }

    protected function generateBrowserTest($name, $url)
    {
        return <<<PHP
<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class {$name} extends DuskTestCase
{
    /**
     * Test basic page functionality
     */
    public function test_page_loads_successfully()
    {
        \$this->browse(function (Browser \$browser) {
            \$browser->visit('{$url}')
                    ->assertPathIs('{$url}')
                    ->assertSee('Laravel');
        });
    }

    /**
     * Test page elements
     */
    public function test_page_contains_expected_elements()
    {
        \$this->browse(function (Browser \$browser) {
            \$browser->visit('{$url}')
                    ->assertVisible('body')
                    ->assertPresent('html');
        });
    }

    /**
     * Test responsive design
     */
    public function test_page_is_responsive()
    {
        \$this->browse(function (Browser \$browser) {
            // Desktop
            \$browser->resize(1920, 1080)
                    ->visit('{$url}')
                    ->assertVisible('body');

            // Mobile
            \$browser->resize(375, 667)
                    ->visit('{$url}')
                    ->assertVisible('body');
        });
    }
}
PHP;
    }

    protected function generateApiTest($name, $url)
    {
        return <<<PHP
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class {$name} extends TestCase
{
    use RefreshDatabase;

    /**
     * Test API endpoint returns successful response
     */
    public function test_api_returns_successful_response()
    {
        \$response = \$this->getJson('{$url}');

        \$response->assertStatus(200)
                 ->assertJsonStructure([
                     // Add expected JSON structure here
                 ]);
    }

    /**
     * Test API endpoint with POST request
     */
    public function test_api_handles_post_request()
    {
        \$data = [
            // Add test data here
        ];

        \$response = \$this->postJson('{$url}', \$data);

        \$response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                 ]);
    }

    /**
     * Test API validation
     */
    public function test_api_validates_input()
    {
        \$response = \$this->postJson('{$url}', []);

        \$response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     // Add expected validation errors
                 ]);
    }
}
PHP;
    }

    protected function generateFeatureTest($name, $url)
    {
        return <<<PHP
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class {$name} extends TestCase
{
    use RefreshDatabase;

    /**
     * Test page loads successfully
     */
    public function test_page_loads_successfully()
    {
        \$response = \$this->get('{$url}');

        \$response->assertStatus(200);
    }

    /**
     * Test page contains expected content
     */
    public function test_page_contains_expected_content()
    {
        \$response = \$this->get('{$url}');

        \$response->assertStatus(200)
                 ->assertSee('Laravel');
    }

    /**
     * Test authenticated access
     */
    public function test_requires_authentication()
    {
        \$response = \$this->get('{$url}');

        // Adjust based on your authentication requirements
        \$response->assertStatus(200);
        // Or: \$response->assertRedirect('/login');
    }

    /**
     * Test form submission
     */
    public function test_handles_form_submission()
    {
        \$data = [
            // Add test data here
        ];

        \$response = \$this->post('{$url}', \$data);

        \$response->assertRedirect()
                 ->assertSessionHas('success');
    }
}
PHP;
    }
}
