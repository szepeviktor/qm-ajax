<?php

declare(strict_types=1);

namespace SzepeViktor\WordPress\QueryMonitor\Ajax;

use QM_Plugin;
use QM_Timer;
use QM_Util;
use WP_Admin_Bar;
use WP_Http;

class HtmlDump extends \QM_Dispatcher_Html {

    /**
     * @var string
     */
    public $id = 'html_dump';

    /**
     * @param \WP_Admin_Bar $wp_admin_bar
     * @return void
     */
    public function action_admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) {
        return;
    }

    /**
     * @return void
     */
    public function dispatch() {
        if ( ! $this->should_dispatch() ) {
            return;
        }

        if ( $this->ceased ) {
            $admin_bar_menu = array(
                'top' => array(
                    'title' => 'Query Monitor',
                ),
                'sub' => array(
                    'ceased' => array(
                        'title' => esc_html__( 'Data collection ceased', 'query-monitor' ),
                        'id' => 'query-monitor-ceased',
                        'href' => '#',
                    ),
                ),
            );

            $json = array(
                'menu' => $admin_bar_menu,
            );

            echo '<!-- Begin Query Monitor output -->' . "\n\n";
            wp_print_inline_script_tag(
                sprintf(
                    'var qm = %s;',
                    wp_json_encode( $json )
                ),
                array(
                    'id' => 'query-monitor-inline-data',
                )
            );
            echo '<div id="query-monitor-ceased"></div>';
            echo '<!-- End Query Monitor output -->' . "\n\n";
            return;
        }

        $switched_locale = self::switch_to_locale( get_user_locale() );

        // Redirect output to an html file
        ob_start();

        // wp_enqueue_scripts is not fired in AJAX requests
        $this->enqueue_assets();

        echo '<!DOCTYPE html>';
        wp_styles()->do_item('query-monitor');
        wp_scripts()->do_item('jquery-core');
        wp_scripts()->do_item('query-monitor');

        $this->before_output();

        foreach ( $this->outputters as $id => $output ) {
            $timer = new QM_Timer();
            $timer->start();

            printf(
                "\n" . '<!-- Begin %1$s output -->' . "\n" . '<div class="qm-panel-container" id="qm-%1$s-container">' . "\n",
                esc_html( $id )
            );
            $output->output();
            printf(
                "\n" . '</div>' . "\n" . '<!-- End %s output -->' . "\n",
                esc_html( $id )
            );

            $output->set_timer( $timer->stop() );
        }

        $this->after_output();

        file_put_contents(
            sprintf(
                '%s/qm-%s-%s.html',
                WP_CONTENT_DIR,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['REQUEST_TIME_FLOAT']
            ),
            ob_get_clean()
        );

        if ( $switched_locale ) {
            self::restore_previous_locale();
        }

    }

    /**
     * @return bool
     */
    public static function request_supported() {
        // Do dispatch if this is an async request:
        if ( QM_Util::is_async() ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function is_active() {
        // Run for everyone
        // if ( ! self::user_can_view() ) {
        //     return false;
        // }

        // Run for users qith QM cookie
        // if ( ! self::user_verified() ) {
        //     return false;
        // }

        // Run for a certain IP address defined in QM_AJAX_IP_ADDRESS
        // if (defined('QM_AJAX_IP_ADDRESS') && WP_Http::is_ip_address(QM_AJAX_IP_ADDRESS)) {
        //     if (QM_AJAX_IP_ADDRESS !== $_SERVER['REMOTE_ADDR'] ?? '') {
        //         return false;
        //     }
        // }

        if ( ! self::request_supported() ) {
            return false;
        }

        // Don't dispatch if the minimum required actions haven't fired:
        if ( is_admin() ) {
            if ( ! did_action( 'admin_init' ) ) {
                return false;
            }
        } else {
            if ( ! ( did_action( 'wp' ) || did_action( 'login_init' ) || did_action( 'gp_head' ) ) ) {
                return false;
            }
        }

        /** Back-compat filter. Please use `qm/dispatch/html` instead */
        if ( ! apply_filters( 'qm/process', true, is_admin_bar_showing() ) ) {
            return false;
        }

        if ( ! file_exists( $this->qm->plugin_path( 'assets/query-monitor.css' ) ) ) {
            return false;
        }

        return true;

    }

}
