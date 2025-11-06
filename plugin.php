<?php

/**
 * Tab Swap Plugin for osTicket
 *
 * Swaps the order of Reply and Internal Note tabs in ticket view,
 * making Internal Note the default active tab.
 *
 * @version 1.0.0
 * @author Markus Michalski
 * @license GPL v2
 */

return array(
    'id' =>             'net.markus-michalski:tab-swap',
    'version' =>        '1.0.1',
    'name' =>           'Tab Swap',
    'author' =>         'Markus Michalski',
    'description' =>    'Swaps Reply and Internal Note tabs in ticket view, making Internal Note the default active tab',
    'url' =>            'https://github.com/markusmichalski/osticket-plugins',
    'plugin' =>         'class.TabSwapPlugin.php:TabSwapPlugin'
);
