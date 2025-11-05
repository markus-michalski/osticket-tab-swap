<?php

namespace TabSwap\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test Signal Callback (JavaScript Injection)
 *
 * RED Phase: These tests will fail initially
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
     * RED: Test JavaScript injection for Ticket objects
     */
    public function it_injects_javascript_for_ticket_objects(): void
    {
        $plugin = new \TabSwapPlugin();

        // Mock Ticket object
        $ticket = $this->createMock(\Ticket::class);

        // Mock staff with reply permission
        global $thisstaff;
        $thisstaff = new class {
            public function hasPerm($perm) {
                return true; // Has PERM_REPLY
            }
        };

        // Capture output
        ob_start();
        $plugin->onObjectView($ticket);
        $output = ob_get_clean();

        // Assert: JavaScript should be injected
        $this->assertStringContainsString(
            '<script',
            $output,
            'Plugin should inject JavaScript for Ticket objects'
        );

        $this->assertStringContainsString(
            'tab-swap.js',
            $output,
            'Plugin should inject tab-swap.js file'
        );
    }

    /**
     * @test
     * RED: Test no injection for non-Ticket objects
     */
    public function it_does_not_inject_for_non_ticket_objects(): void
    {
        $plugin = new \TabSwapPlugin();

        // Mock non-Ticket object (e.g., Task)
        $task = new class {};

        // Capture output
        ob_start();
        $plugin->onObjectView($task);
        $output = ob_get_clean();

        // Assert: No JavaScript should be injected
        $this->assertEmpty(
            $output,
            'Plugin should NOT inject JavaScript for non-Ticket objects'
        );
    }

    /**
     * @test
     * RED: Test no injection when staff has no reply permission
     */
    public function it_does_not_inject_when_no_reply_permission(): void
    {
        $plugin = new \TabSwapPlugin();

        $ticket = $this->createMock(\Ticket::class);

        // Mock staff WITHOUT reply permission
        global $thisstaff;
        $thisstaff = new class {
            public function hasPerm($perm) {
                return false; // No PERM_REPLY
            }
        };

        // Capture output
        ob_start();
        $plugin->onObjectView($ticket);
        $output = ob_get_clean();

        // Assert: No JavaScript should be injected
        $this->assertEmpty(
            $output,
            'Plugin should NOT inject when user lacks reply permission'
        );
    }

    /**
     * @test
     * RED: Test cache-busting parameter in script URL
     */
    public function it_includes_version_in_script_url(): void
    {
        $plugin = new \TabSwapPlugin();

        // Mock config with version
        $config = $this->createMock(\PluginConfig::class);
        $config->method('get')
            ->with('installed_version')
            ->willReturn('1.0.0');

        $reflection = new \ReflectionClass($plugin);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($plugin, $config);

        $ticket = $this->createMock(\Ticket::class);

        global $thisstaff;
        $thisstaff = new class {
            public function hasPerm($perm) {
                return true;
            }
        };

        ob_start();
        $plugin->onObjectView($ticket);
        $output = ob_get_clean();

        // Assert: Version should be in URL
        $this->assertStringContainsString(
            'v=1.0.0',
            $output,
            'Script URL should include version for cache-busting'
        );
    }
}
