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
 */
class TabSwapPlugin extends Plugin {
    var $config_class = 'TabSwapConfig';

    /**
     * Only one instance of this plugin makes sense
     */
    function isSingleton() {
        return true;
    }

    /**
     * Bootstrap plugin - called when osTicket initializes
     */
    function bootstrap() {
        // Version tracking and auto-update
        $this->checkVersion();

        // Only connect signal if plugin is enabled
        if ($this->getConfig()->get('enabled')) {
            Signal::connect('object.view', array($this, 'onObjectView'));
        }
    }

    /**
     * Check plugin version and perform updates if needed
     */
    function checkVersion() {
        $plugin_file = INCLUDE_DIR . 'plugins/' . basename(dirname(__FILE__)) . '/plugin.php';

        if (!file_exists($plugin_file)) {
            return;
        }

        $plugin_info = include($plugin_file);
        $current_version = $plugin_info['version'];
        $installed_version = $this->getConfig()->get('installed_version');

        if (!$installed_version || version_compare($installed_version, $current_version, '<')) {
            $this->performUpdate($installed_version, $current_version);
        }
    }

    /**
     * Perform plugin update
     */
    function performUpdate($from_version, $to_version) {
        // Update /include/.htaccess to allow static assets
        $this->updateIncludeHtaccess();

        // Save new version
        $this->getConfig()->set('installed_version', $to_version);
    }

    /**
     * Update /include/.htaccess to allow static assets for plugins
     * Supports both Apache 2.2 and 2.4 syntax
     */
    function updateIncludeHtaccess() {
        $htaccess_file = INCLUDE_DIR . '.htaccess';

        if (!file_exists($htaccess_file)) {
            return;
        }

        $htaccess_content = file_get_contents($htaccess_file);

        // Check if static assets rule already exists
        if (strpos($htaccess_content, 'Allow static assets for plugins') !== false) {
            return;
        }

        // Find insertion point - either Apache 2.4 style or 2.2 style
        // Look for the end of the deny block (either </IfModule> or "Deny from all")
        $pattern = null;
        if (preg_match('/(# Apache 2\.2\s+<IfModule !mod_authz_core\.c>.*?<\/IfModule>)/s', $htaccess_content)) {
            // Apache 2.4 compatible format with IfModule blocks
            $pattern = '/(# Apache 2\.2\s+<IfModule !mod_authz_core\.c>.*?<\/IfModule>)/s';
        } elseif (preg_match('/(Deny from all)/', $htaccess_content)) {
            // Old Apache 2.2 format
            $pattern = '/(Deny from all)/';
        }

        if (!$pattern || !preg_match($pattern, $htaccess_content)) {
            return;
        }

        // Generate Apache 2.2 and 2.4 compatible rules
        $new_rule = "\n\n# Allow static assets for plugins (JavaScript, CSS, images, fonts)\n";
        $new_rule .= "<FilesMatch \"\\.(js|css|map|json|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|otf)\$\">\n";
        $new_rule .= "    # Apache 2.4\n";
        $new_rule .= "    <IfModule mod_authz_core.c>\n";
        $new_rule .= "        Require all granted\n";
        $new_rule .= "    </IfModule>\n\n";
        $new_rule .= "    # Apache 2.2\n";
        $new_rule .= "    <IfModule !mod_authz_core.c>\n";
        $new_rule .= "        Order allow,deny\n";
        $new_rule .= "        Allow from all\n";
        $new_rule .= "    </IfModule>\n";
        $new_rule .= "</FilesMatch>";

        $new_content = preg_replace(
            $pattern,
            "$1" . $new_rule,
            $htaccess_content,
            1 // Only replace first occurrence
        );

        file_put_contents($htaccess_file, $new_content);
    }

    /**
     * Called when plugin is enabled in admin panel
     */
    function enable() {
        $errors = array();

        // Auto-create instance for singleton plugin
        if ($this->isSingleton() && $this->getNumInstances() === 0) {
            $vars = array(
                'name' => $this->getName(),
                'isactive' => 1,
                'notes' => 'Auto-created singleton instance'
            );

            if (!$this->addInstance($vars, $errors)) {
                return $errors;
            }
        }

        // Update /include/.htaccess to allow static assets for plugins
        $this->updateIncludeHtaccess();

        // Get current version from plugin.php
        $plugin_file = INCLUDE_DIR . 'plugins/' . basename(dirname(__FILE__)) . '/plugin.php';

        if (file_exists($plugin_file)) {
            $plugin_info = include($plugin_file);
            $this->getConfig()->set('installed_version', $plugin_info['version']);
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Called when plugin is disabled in admin panel
     */
    function disable() {
        // Remove static assets rule from /include/.htaccess
        $htaccess_file = INCLUDE_DIR . '.htaccess';

        if (file_exists($htaccess_file)) {
            $htaccess_content = file_get_contents($htaccess_file);

            // Check if our rule exists
            if (strpos($htaccess_content, 'Allow static assets for plugins') !== false) {
                // Remove the FilesMatch block we added
                $pattern = '/\n\n# Allow static assets for plugins.*?<\/FilesMatch>/s';
                $new_content = preg_replace($pattern, '', $htaccess_content, 1);
                file_put_contents($htaccess_file, $new_content);
            }
        }

        return true;
    }

    /**
     * Signal handler for object.view
     *
     * @param object $object The object being viewed (Ticket, Task, etc.)
     */
    function onObjectView($object) {
        // Only handle Ticket objects
        if (!($object instanceof Ticket)) {
            return;
        }

        // Inject JavaScript to perform tab swap
        // The JavaScript will check if both tabs exist before swapping
        $this->injectScript();
    }

    /**
     * Inject JavaScript file into page
     */
    function injectScript() {
        $script_url = $this->getPluginUrl() . '/js/tab-swap.js';

        // Add cache-busting parameter with plugin version
        $version = $this->getConfig()->get('installed_version');
        if ($version) {
            $script_url .= '?v=' . urlencode($version);
        }

        // Security: Escape output to prevent XSS
        echo '<script src="' . htmlspecialchars($script_url, ENT_QUOTES, 'UTF-8') . '"></script>';
    }

    /**
     * Get plugin base URL
     *
     * @return string Plugin URL (e.g., /include/plugins/tab-swap)
     */
    function getPluginUrl() {
        $plugin_dir = basename(dirname(__FILE__));
        return ROOT_PATH . 'include/plugins/' . $plugin_dir;
    }
}
