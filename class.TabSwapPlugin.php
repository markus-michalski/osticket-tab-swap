<?php

// Only include osTicket classes if they exist (not in test environment)
if (defined('INCLUDE_DIR') && file_exists(INCLUDE_DIR . 'class.plugin.php')) {
    require_once INCLUDE_DIR . 'class.plugin.php';
}

if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Tab Swap Plugin for osTicket 1.18.x
 *
 * Swaps the order of Reply and Internal Note tabs in ticket view,
 * making Internal Note the default active tab.
 *
 * Key Design Decisions:
 * - Uses inline JavaScript instead of external file (no .htaccess modification needed)
 * - Follows osTicket signal pattern for event handling
 * - Implements singleton pattern for single-instance plugins
 */
class TabSwapPlugin extends Plugin {

    /** @var string Configuration class name */
    var $config_class = 'TabSwapConfig';

    /**
     * Only one instance of this plugin makes sense
     *
     * @return bool Always true - this plugin is a singleton
     */
    function isSingleton(): bool {
        return true;
    }

    /**
     * Bootstrap plugin - called when osTicket initializes
     *
     * Registers signal handlers if plugin is enabled.
     * Version tracking ensures seamless updates.
     */
    function bootstrap(): void {
        $this->checkVersion();

        if ($this->getConfig()->get('enabled')) {
            Signal::connect('object.view', array($this, 'onObjectView'));
        }
    }

    /**
     * Check plugin version and perform updates if needed
     *
     * Compares installed version with current plugin.php version
     * and triggers update routine if version mismatch detected.
     */
    function checkVersion(): void {
        $pluginInfo = $this->getPluginInfo();

        if (!$pluginInfo) {
            return;
        }

        $currentVersion = $pluginInfo['version'] ?? null;
        $installedVersion = $this->getConfig()->get('installed_version');

        if (!$installedVersion || version_compare($installedVersion, $currentVersion, '<')) {
            $this->performUpdate($installedVersion, $currentVersion);
        }
    }

    /**
     * Get plugin metadata from plugin.php
     *
     * @return array|null Plugin info array or null if file not found
     */
    protected function getPluginInfo(): ?array {
        $pluginFile = $this->getPluginFilePath();

        if (!file_exists($pluginFile)) {
            return null;
        }

        return include $pluginFile;
    }

    /**
     * Get path to plugin.php file
     *
     * Extracted for testability (can be overridden in tests).
     *
     * @return string Absolute path to plugin.php
     */
    protected function getPluginFilePath(): string {
        return INCLUDE_DIR . 'plugins/' . basename(dirname(__FILE__)) . '/plugin.php';
    }

    /**
     * Perform plugin update
     *
     * @param string|null $fromVersion Previous version
     * @param string|null $toVersion   New version
     */
    function performUpdate(?string $fromVersion, ?string $toVersion): void {
        // Save new version to config
        $this->getConfig()->set('installed_version', $toVersion);

        // Log update only in debug mode (prevents information disclosure)
        if (defined('DEBUG') && DEBUG && function_exists('error_log')) {
            error_log(sprintf(
                'Tab Swap Plugin: Updated from %s to %s',
                $fromVersion ?? 'new install',
                $toVersion
            ));
        }
    }

    /**
     * Called when plugin is enabled in admin panel
     *
     * Creates singleton instance automatically and saves current version.
     *
     * @return bool|array True on success, array of errors on failure
     */
    function enable() {
        $errors = array();

        // Auto-create instance for singleton plugin (osTicket Best Practice)
        if ($this->isSingleton() && $this->getNumInstances() === 0) {
            $vars = array(
                'name' => $this->getName(),
                'isactive' => 1,
                'notes' => 'Auto-created singleton instance'
            );

            if (!$this->addInstance($vars, $errors)) {
                // Log failure in debug mode only (avoid information disclosure)
                if (defined('DEBUG') && DEBUG && function_exists('error_log')) {
                    error_log('Tab Swap Plugin: Failed to create instance');
                    // Note: $errors not logged - may contain sensitive data
                }
                return $errors;
            }
        }

        // Save current version to config
        $pluginInfo = $this->getPluginInfo();
        if ($pluginInfo && isset($pluginInfo['version'])) {
            $this->getConfig()->set('installed_version', $pluginInfo['version']);
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Called when plugin is disabled in admin panel
     *
     * @return bool Always true
     */
    function disable(): bool {
        return true;
    }

    /**
     * Signal handler for object.view
     *
     * Injects tab swap JavaScript when viewing a Ticket.
     * Guards against non-Ticket objects.
     *
     * @param object $object The object being viewed (Ticket, Task, etc.)
     */
    function onObjectView($object): void {
        if (!($object instanceof Ticket)) {
            return;
        }

        $this->injectScript();
    }

    /**
     * Inject inline JavaScript to perform tab swap
     *
     * Uses inline script instead of external file to avoid .htaccess modifications.
     * JavaScript is minified and includes all necessary guards.
     */
    function injectScript(): void {
        // Get and validate version (prevents XSS via malformed config data)
        $version = $this->getConfig()->get('installed_version') ?? '1.0.0';
        if (!preg_match('/^\d+\.\d+(\.\d+)?$/', $version)) {
            $version = '1.0.0';
        }

        $script = $this->getInlineScript();

        // CSP Nonce support (future-proof for stricter osTicket CSP policies)
        $nonce = defined('CSP_NONCE')
            ? sprintf(' nonce="%s"', htmlspecialchars(CSP_NONCE, ENT_QUOTES, 'UTF-8'))
            : '';

        echo sprintf(
            '<script data-plugin="tab-swap" data-version="%s"%s>%s</script>',
            htmlspecialchars($version, ENT_QUOTES, 'UTF-8'),
            $nonce,
            $script
        );
    }

    /**
     * Get inline JavaScript for tab swapping
     *
     * Self-contained script that:
     * - Uses MutationObserver for reliable DOM detection
     * - Guards against missing elements
     * - Supports PJAX navigation
     * - Has no external dependencies
     *
     * @return string Minified JavaScript code
     */
    protected function getInlineScript(): string {
        // Minified but readable JavaScript
        return <<<'JS'
(function(){
    "use strict";

    var TabSwap = {
        done: false,

        init: function() {
            if (this.done) return;

            var replyTab = document.getElementById('post-reply-tab');
            var noteTab = document.getElementById('post-note-tab');
            var replyForm = document.getElementById('reply');
            var noteForm = document.getElementById('note');

            // Guard: Required elements must exist
            if (!replyTab || !noteTab || !replyForm || !noteForm) return;

            // Guard: Tabs must be visible (permission check)
            if (replyTab.offsetParent === null || noteTab.offsetParent === null) return;

            // 1. Swap tab order
            var tabs = document.getElementById('response-tabs');
            var replyLi = replyTab.parentNode;
            var noteLi = noteTab.parentNode;
            if (tabs && replyLi && noteLi) {
                tabs.insertBefore(noteLi, replyLi);
            }

            // 2. Switch active state
            replyTab.className = replyTab.className.replace(/\bactive\b/g, '').trim();
            replyTab.setAttribute('aria-selected', 'false');
            noteTab.className = noteTab.className + ' active';
            noteTab.setAttribute('aria-selected', 'true');

            // 3. Switch form visibility
            replyForm.style.display = 'none';
            noteForm.style.display = 'block';

            this.done = true;
        },

        reset: function() {
            this.done = false;
        }
    };

    // Use MutationObserver for reliable detection
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations, obs) {
            if (document.getElementById('post-reply-tab')) {
                TabSwap.init();
                if (TabSwap.done) obs.disconnect();
            }
        });
        observer.observe(document.body || document.documentElement, {
            childList: true,
            subtree: true
        });
    }

    // Fallbacks
    TabSwap.init();
    document.addEventListener('DOMContentLoaded', function() { TabSwap.init(); });

    // PJAX support
    document.addEventListener('pjax:success', function() {
        TabSwap.reset();
        TabSwap.init();
    });

    // Legacy osTicket event
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('redraw.staff', function() {
            TabSwap.reset();
            TabSwap.init();
        });
    }
})();
JS;
    }
}
