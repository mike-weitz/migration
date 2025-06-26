<?php

/**
 * Plugin Name: NEOnet Events Certificates (Internal)
 * Description: Custom WordPress plugin to build and display certificates for users after attending events
 * Version: 1.0.0
 * Author: NEOnet
 * Text Domain: neonet-certificates-api
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define Constants
define('NEONET_CERTIFICATES_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('NEONET_CERTIFICATES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once NEONET_CERTIFICATES_PLUGIN_PATH . 'includes/class-neonet-certificates.php';


// ---- Instantiate Core Plugin Classes ----

function get_certificates_instance()
{
    static $instance;
    if (!$instance) {
        $instance = new \NEOnet_Events\Certificates\Certificates();
    }
    return $instance;
}

// Instantiate the main plugin class
function neonet_events_certificates_init()
{
    get_certificates_instance();
}
add_action('plugins_loaded', 'neonet_events_certificates_init');

// ---- Plugin Lifecycle Hooks ----

// Perform actions needed on plugin activation
function activate_neonet_events_certificates()
{
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\activate_neonet_events_certificates');

// Perform actions needed on plugin deactivation
function deactivate_neonet_events_certificates()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivate_neonet_events_certificates');
