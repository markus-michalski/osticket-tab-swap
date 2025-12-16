<?php

namespace TabSwap\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test Inline JavaScript Generation
 *
 * Ensures the inline script contains all required functionality.
 */
class InlineScriptTest extends TestCase
{
    private \TabSwapPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../class.TabSwapPlugin.php';
        require_once __DIR__ . '/../../config.php';

        $this->plugin = new \TabSwapPlugin();
    }

    /**
     * @test
     * Verifies inline script contains IIFE wrapper
     */
    public function it_wraps_script_in_iife(): void
    {
        $script = $this->getInlineScript();

        $this->assertStringStartsWith('(function(){', $script);
        $this->assertStringEndsWith('})();', $script);
    }

    /**
     * @test
     * Verifies inline script uses strict mode
     */
    public function it_uses_strict_mode(): void
    {
        $script = $this->getInlineScript();

        $this->assertStringContainsString('"use strict"', $script);
    }

    /**
     * @test
     * Verifies inline script contains MutationObserver pattern
     */
    public function it_uses_mutation_observer(): void
    {
        $script = $this->getInlineScript();

        $this->assertStringContainsString('MutationObserver', $script);
        $this->assertStringContainsString('observer.observe', $script);
        $this->assertStringContainsString('disconnect', $script);
    }

    /**
     * @test
     * Verifies inline script contains all required DOM element IDs
     */
    public function it_references_required_dom_elements(): void
    {
        $script = $this->getInlineScript();

        $requiredElements = [
            'post-reply-tab',
            'post-note-tab',
            'reply',
            'note',
            'response-tabs'
        ];

        foreach ($requiredElements as $element) {
            $this->assertStringContainsString(
                $element,
                $script,
                "Script should reference DOM element: $element"
            );
        }
    }

    /**
     * @test
     * Verifies inline script handles visibility check
     */
    public function it_checks_element_visibility(): void
    {
        $script = $this->getInlineScript();

        // Uses offsetParent for visibility check
        $this->assertStringContainsString('offsetParent', $script);
    }

    /**
     * @test
     * Verifies inline script updates ARIA attributes
     */
    public function it_updates_aria_attributes(): void
    {
        $script = $this->getInlineScript();

        $this->assertStringContainsString('aria-selected', $script);
    }

    /**
     * @test
     * Verifies inline script handles PJAX navigation
     */
    public function it_handles_pjax_navigation(): void
    {
        $script = $this->getInlineScript();

        $this->assertStringContainsString('pjax:success', $script);
        $this->assertStringContainsString('reset', $script);
    }

    /**
     * @test
     * Verifies inline script handles legacy osTicket events
     */
    public function it_handles_legacy_osticket_events(): void
    {
        $script = $this->getInlineScript();

        $this->assertStringContainsString('redraw.staff', $script);
    }

    /**
     * @test
     * Verifies inline script has DOMContentLoaded fallback
     */
    public function it_has_dom_content_loaded_fallback(): void
    {
        $script = $this->getInlineScript();

        $this->assertStringContainsString('DOMContentLoaded', $script);
    }

    /**
     * @test
     * Verifies inline script has done flag to prevent double execution
     */
    public function it_has_done_flag_guard(): void
    {
        $script = $this->getInlineScript();

        $this->assertStringContainsString('done: false', $script);
        $this->assertStringContainsString('this.done = true', $script);
        $this->assertStringContainsString('if (this.done) return', $script);
    }

    /**
     * Helper to get inline script via reflection
     */
    private function getInlineScript(): string
    {
        $reflection = new \ReflectionClass($this->plugin);
        $method = $reflection->getMethod('getInlineScript');
        $method->setAccessible(true);

        return $method->invoke($this->plugin);
    }
}
