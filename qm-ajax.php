<?php

/**
 * Query Monitor dumper for AJAX requests
 *
 * @wordpress-plugin
 * Plugin Name:       Query Monitor dumper for AJAX requests
 * Plugin URI:        https://github.com/szepeviktor/qm-ajax
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

use QM_Collectors;
use QM_Plugin;

require_once __DIR__.'/src/RequestData.php';
require_once __DIR__.'/src/RequestCollector.php';
require_once __DIR__.'/src/RequestOutput.php';

add_filter(
    'qm/dispatchers',
    static function (array $dispatchers, QM_Plugin $qm) {
        // Needs query-monitor/dispatchers/Html.php loaded
        require_once __DIR__.'/src/HtmlDump.php';

        $dispatchers['html'] = new HtmlDump($qm);
        return $dispatchers;
    },
    10,
    2
);

add_filter(
    'qm/collectors',
    static function (array $collectors): array
    {
        $collectors['request_vars'] = new RequestCollector();

        return $collectors;
    },
    20,
    1
);

add_filter(
    'qm/outputter/html',
    static function (array $output, QM_Collectors $collectors) {
        $collector = QM_Collectors::get('request_vars');
        if ($collector) {
            $output['request_vars'] = new RequestOutput($collector);
        }

        return $output;
    },
    10,
    2
);
