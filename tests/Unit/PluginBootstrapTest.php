<?php

namespace TabSwap\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test Plugin Bootstrap Process
 *
 * RED Phase: These tests will initially fail
 */
class PluginBootstrapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset Signal handlers before each test
        \Signal::reset();

        // Include plugin files
        require_once __DIR__ . '/../../class.TabSwapPlugin.php';
        require_once __DIR__ . '/../../config.php';
    }

    /**
     * @test
     * RED: This test will fail initially because Signal handler is not registered yet
     */
    public function it_registers_object_view_signal_when_enabled(): void
    {
        $plugin = new \TabSwapPlugin();

        // Simulate enabled config
        $config = $this->createMock(\PluginConfig::class);
        $config->method('get')
            ->willReturnMap([
                ['enabled', null, true],
                ['installed_version', null, '1.0.0']
            ]);

        // Use reflection to set private config
        $reflection = new \ReflectionClass($plugin);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($plugin, $config);

        // Bootstrap plugin
        $plugin->bootstrap();

        // Assert: Signal handler should be registered
        $this->assertTrue(
            \Signal::hasHandler('object.view'),
            'Plugin should register object.view signal handler when enabled'
        );
    }

    /**
     * @test
     * RED: This test verifies plugin respects enabled config
     */
    public function it_does_not_register_signal_when_disabled(): void
    {
        $plugin = new \TabSwapPlugin();

        // Simulate disabled config
        $config = $this->createMock(\PluginConfig::class);
        $config->method('get')
            ->willReturnMap([
                ['enabled', null, false],
                ['installed_version', null, '1.0.0']
            ]);

        $reflection = new \ReflectionClass($plugin);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($plugin, $config);

        $plugin->bootstrap();

        // Assert: No signal handler should be registered
        $this->assertFalse(
            \Signal::hasHandler('object.view'),
            'Plugin should NOT register signal when disabled'
        );
    }

    /**
     * @test
     * RED: Verify singleton pattern
     */
    public function it_is_singleton(): void
    {
        $plugin = new \TabSwapPlugin();

        $this->assertTrue(
            $plugin->isSingleton(),
            'Plugin should be a singleton'
        );
    }
}
