<?php

/**
 * Query Monitor dumper for AJAX requests
 *
 * @wordpress-plugin
 * Plugin Name:       Query Monitor dumper for AJAX requests
 * Plugin URI:        https://github.com/szepeviktor/qm-opcache-ajax
 * Description:       Dump HTML output to a file (wp-content/qm-*.html)
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  query-monitor
 * Author:            Viktor Szépe
 * Author URI:        https://github.com/szepeviktor
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

declare(strict_types=1);

namespace SzepeViktor\WordPress\QueryMonitor\Ajax;

use QM_Plugin;

require_once WP_PLUGIN_DIR.'/query-monitor/dispatchers/Html.php';
require_once __DIR__.'/src/HtmlDump.php';

add_filter(
    'qm/dispatchers',
    static function (array $dispatchers, QM_Plugin $qm) {
        $dispatchers['html'] = new HtmlDump($qm);
        return $dispatchers;
    },
    10,
    2
);
