<?php

/**
 * PHPUnit Bootstrap File
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Define constants that osTicket normally provides
if (!defined('INCLUDE_DIR')) {
    define('INCLUDE_DIR', __DIR__ . '/../../osTicket/include/');
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', '/');
}

// Mock osTicket classes for testing (order matters!)

class PluginConfig {
    private $config = [];

    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    public function set($key, $value) {
        $this->config[$key] = $value;
        return true;
    }

    public static function translate($plugin) {
        return [
            function($x) { return $x; },
            function($x, $y, $n) { return $n != 1 ? $y : $x; }
        ];
    }
}

class Plugin {
    protected $config;

    public function __construct() {
        $this->config = new PluginConfig();
    }

    public function getConfig() {
        return $this->config;
    }

    public function getPluginUrl() {
        return '/include/plugins/tab-swap';
    }
}

class Signal {
    private static $handlers = [];

    public static function connect($signal, $callback) {
        self::$handlers[$signal][] = $callback;
    }

    public static function send($signal, ...$args) {
        if (isset(self::$handlers[$signal])) {
            foreach (self::$handlers[$signal] as $handler) {
                call_user_func_array($handler, $args);
            }
        }
    }

    public static function hasHandler($signal) {
        return isset(self::$handlers[$signal]) && count(self::$handlers[$signal]) > 0;
    }

    public static function reset() {
        self::$handlers = [];
    }
}

class Ticket {
    const PERM_REPLY = 1;
}

class BooleanField {
    public function __construct($config) {}
}

class TextboxField {
    public function __construct($config) {}
}

class VisibilityConstraint {
    const HIDDEN = 1;
    public function __construct($query, $type) {}
}

class Q {
    public function __construct($conditions) {}
}
