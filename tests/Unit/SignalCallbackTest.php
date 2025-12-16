<?php

namespace TabSwap\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test Signal Callback (JavaScript Injection)
 *
 * Tests verify inline JavaScript injection behavior.
 */
class SignalCallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Include plugin files
        require_once __DIR__ . '/../../class.TabSwapPlugin.php';
        require_once __DIR__ . '/../../config.php';
    }

    /**
     * @test
     * Verifies inline JavaScript is injected for Ticket objects
     */
    public function it_injects_inline_javascript_for_ticket_objects(): void
    {
        $plugin = new \TabSwapPlugin();
        $ticket = $this->createMock(\Ticket::class);

        ob_start();
        $plugin->onObjectView($ticket);
        $output = ob_get_clean();

        // Assert: Inline script should be injected
        $this->assertStringContainsString(
            '<script data-plugin="tab-swap"',
            $output,
            'Plugin should inject inline JavaScript for Ticket objects'
        );

        // Assert: Script contains tab swap logic
        $this->assertStringContainsString(
            'TabSwap',
            $output,
            'Inline script should contain TabSwap object'
        );

        // Assert: Script contains MutationObserver
        $this->assertStringContainsString(
            'MutationObserver',
            $output,
            'Inline script should use MutationObserver for DOM detection'
        );
    }

    /**
     * @test
     * Verifies no injection for non-Ticket objects
     */
    public function it_does_not_inject_for_non_ticket_objects(): void
    {
        $plugin = new \TabSwapPlugin();
        $task = new class {};

        ob_start();
        $plugin->onObjectView($task);
        $output = ob_get_clean();

        $this->assertEmpty(
            $output,
            'Plugin should NOT inject JavaScript for non-Ticket objects'
        );
    }

    /**
     * @test
     * Verifies version is included in script data attribute
     */
    public function it_includes_version_in_script_data_attribute(): void
    {
        $plugin = new \TabSwapPlugin();

        // Mock config with version
        $config = $this->createMock(\PluginConfig::class);
        $config->method('get')
            ->willReturnCallback(function($key) {
                if ($key === 'installed_version') {
                    return '1.0.0';
                }
                return null;
            });

        $reflection = new \ReflectionClass($plugin);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($plugin, $config);

        $ticket = $this->createMock(\Ticket::class);

        ob_start();
        $plugin->onObjectView($ticket);
        $output = ob_get_clean();

        // Assert: Version should be in data attribute
        $this->assertStringContainsString(
            'data-version="1.0.0"',
            $output,
            'Script tag should include version in data-version attribute'
        );
    }

    /**
     * @test
     * Verifies inline script contains PJAX support
     */
    public function it_includes_pjax_support_in_inline_script(): void
    {
        $plugin = new \TabSwapPlugin();
        $ticket = $this->createMock(\Ticket::class);

        ob_start();
        $plugin->onObjectView($ticket);
        $output = ob_get_clean();

        $this->assertStringContainsString(
            'pjax:success',
            $output,
            'Inline script should include PJAX support'
        );
    }

    /**
     * @test
     * Verifies inline script has guard clauses
     */
    public function it_includes_guard_clauses_in_inline_script(): void
    {
        $plugin = new \TabSwapPlugin();
        $ticket = $this->createMock(\Ticket::class);

        ob_start();
        $plugin->onObjectView($ticket);
        $output = ob_get_clean();

        // Check for element existence guards
        $this->assertStringContainsString(
            'post-reply-tab',
            $output,
            'Inline script should reference reply tab'
        );

        $this->assertStringContainsString(
            'post-note-tab',
            $output,
            'Inline script should reference note tab'
        );
    }

    /**
     * @test
     * Verifies malformed version is rejected and fallback used
     */
    public function it_sanitizes_malformed_version(): void
    {
        $plugin = new \TabSwapPlugin();

        // Mock config with XSS attempt in version
        $config = $this->createMock(\PluginConfig::class);
        $config->method('get')
            ->willReturnCallback(function($key) {
                if ($key === 'installed_version') {
                    return '"><script>alert(1)</script>';
                }
                return null;
            });

        $reflection = new \ReflectionClass($plugin);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($plugin, $config);

        $ticket = $this->createMock(\Ticket::class);

        ob_start();
        $plugin->onObjectView($ticket);
        $output = ob_get_clean();

        // Assert: Malformed version should be replaced with fallback
        $this->assertStringContainsString(
            'data-version="1.0.0"',
            $output,
            'Malformed version should be sanitized to fallback 1.0.0'
        );

        // Assert: No XSS payload in output
        $this->assertStringNotContainsString(
            'alert(1)',
            $output,
            'XSS payload should not appear in output'
        );
    }
}
