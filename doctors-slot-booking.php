<?php
/**
 * Plugin Name: Doctors Slot Booking
 * Version: 1.0.0
 * Description: Doctors Slot Booking will help to manage booking according to doctors availability.
 * Author: Rahul K
 * Author URI: 
 * License: GPLv2 or later
 * Requires at least: 5.2
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Text Domain: doctors-slot-booking
 */
 
defined('ABSPATH') || exit;

if (!defined('DSLB_FILE')) {
    define('DSLB_FILE', __FILE__);
}

define('DSLB_VERSION', '1.0.0');
define('DSLB_PLUGIN_NAME', 'Doctors Slot Booking');

define('DSLB_TOKEN', 'dslb');
define('DSLB_PATH', plugin_dir_path(DSLB_FILE));
define('DSLB_URL', plugins_url('/', DSLB_FILE));

define('DSLB_ASSETS_PATH', DSLB_PATH . 'assets/');
define('DSLB_ASSETS_URL', DSLB_URL . 'assets/');
define('DSLB_INCLUDES_PATH', DSLB_PATH . 'includes/');
define('DSLB_LIBRARY_PATH', DSLB_INCLUDES_PATH . 'lib/');

add_action('plugins_loaded', 'dslb_load_plugin_textdomain');

if (!version_compare(PHP_VERSION, '7.4', '>=')) {
    add_action('admin_notices', 'dslb_php_version_check_fail');
} elseif (!version_compare(get_bloginfo('version'), '5.2', '>=')) {
    add_action('admin_notices', 'dslb_wp_version_check_fail');
} else {
    require DSLB_INCLUDES_PATH . 'main.php';
}


/**
 * Load Plugin textdomain.
 *
 * Load gettext translate for Plugin text domain.
 *
 * @return void
 * @since 1.0.0
 *
 */
function dslb_load_plugin_textdomain(){
    load_plugin_textdomain('doctors-slot-booking');
}

/**
 * Plugin admin notice for minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @return void
 * @since 1.0.0
 *
 */
function dslb_php_version_check_fail(){
    /* translators: %s: PHP version. */
    $message = sprintf(esc_html__('%1$s requires PHP version %2$s+, plugin is currently not running.', 'doctors-slot-booking'), DSLB_PLUGIN_NAME, '7.4');
    $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($html_message);
}

/**
 * Plugin admin notice for minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @return void
 * @since 1.0.0
 *
 */
function dslb_wp_version_check_fail(){
    /* translators: %s: WordPress version. */
    $message = sprintf(esc_html__('%1$s requires WordPress version %2$s+. Because you are using an earlier version, the plugin is currently not running.', 'doctors-slot-booking'), DSLB_PLUGIN_NAME, '5.2');
    $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($html_message);
}