<?php
/**
 * Plugin Name: SMS-fly
 * Plugin URI: https://sms-fly.ua
 * Description: SMS and Viber messages using the SMS-fly service
 * Version: 3.0.0
 * Author: SMS-fly Dev
 * Text Domain: smsfly
 * Domain Path: /languages
 * URI: https://sms-fly.ua
 * Requires PHP: 7.3
 */

register_activation_hook(__FILE__, 'smsfly_activate');
register_deactivation_hook(__FILE__, 'smsfly_deactivate');

define('SMSFLY_DIR', plugin_dir_path(__FILE__));
define('SMSFLY_INCL', plugin_dir_path(__FILE__) . '/includes/');
define('SMSFLY_URL', plugin_dir_url(__FILE__));

if(is_admin()) {
    add_action('init', 'wpdocs_load_textdomain');
    function wpdocs_load_textdomain() {
        load_plugin_textdomain('smsfly', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    include(SMSFLY_DIR . 'includes/settings.php');
    include(SMSFLY_DIR . 'includes/logs.php');
}

include(SMSFLY_DIR . 'includes/smsflyc.php');
include(SMSFLY_DIR . 'includes/functions.php');

function smsfly_activate() {
    smsfly_create_log_table();
}

function smsfly_deactivate() {
    // delete_option('SMSFLY_login');
    // delete_option('SMSFLY_pass');
}