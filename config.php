<?php

// Only include osTicket classes if they exist (not in test environment)
if (defined('INCLUDE_DIR') && file_exists(INCLUDE_DIR . 'class.plugin.php')) {
    require_once INCLUDE_DIR . 'class.plugin.php';
}

/**
 * Tab Swap Plugin Configuration
 */
class TabSwapConfig extends PluginConfig {

    /**
     * Translate strings (for i18n support)
     *
     * @param string $plugin Plugin name for translation context
     * @return array{0: callable, 1: callable} Translation functions [$__, $_N]
     */
    static function translate($plugin = 'tab-swap'): array {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function($x) { return $x; },
                function($x, $y, $n) { return $n != 1 ? $y : $x; },
            );
        }

        /** @disregard P1013 (Plugin class may not exist in test environment) */
        return Plugin::translate($plugin);
    }

    /**
     * Build configuration options
     */
    function getOptions() {
        list($__, $_N) = self::translate();

        return array(
            'enabled' => new BooleanField(array(
                'id' => 'enabled',
                'label' => $__('Enable Tab Swap'),
                'configuration' => array(
                    'desc' => $__('When enabled, the Reply and Internal Note tabs will be swapped, making Internal Note the default active tab in ticket view.')
                ),
                'default' => true
            )),

            'installed_version' => new TextboxField(array(
                'id' => 'installed_version',
                'label' => $__('Installed Version'),
                'configuration' => array(
                    'desc' => $__('Currently installed version (automatically updated)'),
                    'size' => 10,
                    'length' => 10,
                    'disabled' => true // Make field read-only instead of hidden
                ),
                'default' => ''
            ))
        );
    }
}
