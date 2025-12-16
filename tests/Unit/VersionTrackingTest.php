<?php

namespace TabSwap\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test Version Tracking and Update Logic
 */
class VersionTrackingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../class.TabSwapPlugin.php';
        require_once __DIR__ . '/../../config.php';
    }

    /**
     * @test
     * Verifies getPluginFilePath returns correct path structure
     */
    public function it_returns_correct_plugin_file_path(): void
    {
        $plugin = new \TabSwapPlugin();

        $reflection = new \ReflectionClass($plugin);
        $method = $reflection->getMethod('getPluginFilePath');
        $method->setAccessible(true);

        $path = $method->invoke($plugin);

        // Should contain expected path components
        $this->assertStringContainsString('plugins', $path);
        $this->assertStringEndsWith('plugin.php', $path);
    }

    /**
     * @test
     * Verifies getPluginInfo returns null for non-existent file
     */
    public function it_returns_null_for_missing_plugin_file(): void
    {
        // Create plugin with overridden path
        $plugin = new class extends \TabSwapPlugin {
            protected function getPluginFilePath(): string {
                return '/non/existent/path/plugin.php';
            }
        };

        $reflection = new \ReflectionClass($plugin);
        $method = $reflection->getMethod('getPluginInfo');
        $method->setAccessible(true);

        $result = $method->invoke($plugin);

        $this->assertNull($result);
    }

    /**
     * @test
     * Verifies performUpdate saves version to config
     */
    public function it_saves_version_on_update(): void
    {
        $plugin = new \TabSwapPlugin();

        // Capture config calls
        $config = $this->createMock(\PluginConfig::class);
        $config->expects($this->once())
            ->method('set')
            ->with('installed_version', '2.0.0');

        $reflection = new \ReflectionClass($plugin);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($plugin, $config);

        $plugin->performUpdate('1.0.0', '2.0.0');
    }

    /**
     * @test
     * Verifies checkVersion triggers update when versions differ
     */
    public function it_triggers_update_when_version_differs(): void
    {
        $updateCalled = false;
        $capturedVersions = [];

        // Create plugin with test hooks
        $plugin = new class($updateCalled, $capturedVersions) extends \TabSwapPlugin {
            private $updateCalledRef;
            private $capturedVersionsRef;

            public function __construct(&$updateCalled, &$capturedVersions)
            {
                parent::__construct();
                $this->updateCalledRef = &$updateCalled;
                $this->capturedVersionsRef = &$capturedVersions;
            }

            protected function getPluginInfo(): ?array
            {
                return ['version' => '2.0.0'];
            }

            function performUpdate(?string $fromVersion, ?string $toVersion): void
            {
                $this->updateCalledRef = true;
                $this->capturedVersionsRef = ['from' => $fromVersion, 'to' => $toVersion];
            }
        };

        // Mock config returning old version
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

        $plugin->checkVersion();

        $this->assertTrue($updateCalled, 'performUpdate should be called');
        $this->assertEquals('1.0.0', $capturedVersions['from']);
        $this->assertEquals('2.0.0', $capturedVersions['to']);
    }

    /**
     * @test
     * Verifies checkVersion skips update when versions match
     */
    public function it_skips_update_when_versions_match(): void
    {
        $updateCalled = false;

        $plugin = new class($updateCalled) extends \TabSwapPlugin {
            private $updateCalledRef;

            public function __construct(&$updateCalled)
            {
                parent::__construct();
                $this->updateCalledRef = &$updateCalled;
            }

            protected function getPluginInfo(): ?array
            {
                return ['version' => '1.0.0'];
            }

            function performUpdate(?string $fromVersion, ?string $toVersion): void
            {
                $this->updateCalledRef = true;
            }
        };

        // Mock config returning same version
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

        $plugin->checkVersion();

        $this->assertFalse($updateCalled, 'performUpdate should NOT be called when versions match');
    }
}
