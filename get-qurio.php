<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://getqurio.com/
 * @since             1.0.0
 * @package           Get_Qurio
 *
 * @wordpress-plugin
 * Plugin Name:       Qurio
 * Plugin URI:        https://github.com/GetQurio/
 * Description:       With Qurio digital media can engage natively on their homepage with their audiences leveraging AI-powered surveys and engagement campaigns to boost trust, email signups, and revenue.
 * Version:           1.0.9
 * Author:            AthensLive Media Solutions
 * Author URI:        https://getqurio.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       get-qurio
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GET_QURIO_VERSION', '1.0.9' );
define( 'GET_QURIO_DIRURL', plugin_dir_url( __FILE__ ) );
define( 'GET_QURIO_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'GET_QURIO_API_ROOT', 'https://main.d3jgd5ppxfn92f.amplifyapp.com/api' );
define( 'GET_QURIO_SANDBOX', 1);
define( 'GET_QURIO_APP_URL', 'https://main.d3jgd5ppxfn92f.amplifyapp.com');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-get-qurio-activator.php
 */
function activate_get_qurio() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-get-qurio-activator.php';
	Get_Qurio_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-get-qurio-deactivator.php
 */
function deactivate_get_qurio() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-get-qurio-deactivator.php';
	Get_Qurio_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_get_qurio' );
register_deactivation_hook( __FILE__, 'deactivate_get_qurio' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-get-qurio.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_get_qurio() {

	$plugin = new Get_Qurio();
	$plugin->run();

}

if (!function_exists('get_qurio_write_log')) {

    function get_qurio_write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

}

run_get_qurio();
