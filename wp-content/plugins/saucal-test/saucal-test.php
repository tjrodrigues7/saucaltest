<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://www.linkedin.com/in/ivan-rodrigues7/
 * @since             1.0.0
 * @package           Saucal_Test
 *
 * @wordpress-plugin
 * Plugin Name:       Saucal Test
 * Plugin URI:        https://https://www.linkedin.com/in/ivan-rodrigues7/
 * Description:       Just a custom plugin for the Saucal test
 * Version:           1.0.0
 * Author:            Ivan Rodrigues
 * Author URI:        https://https://www.linkedin.com/in/ivan-rodrigues7//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       saucal-test
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
define( 'SAUCAL_TEST_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-saucal-test-activator.php
 */
function activate_saucal_test() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-saucal-test-activator.php';
	Saucal_Test_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-saucal-test-deactivator.php
 */
function deactivate_saucal_test() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-saucal-test-deactivator.php';
	Saucal_Test_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_saucal_test' );
register_deactivation_hook( __FILE__, 'deactivate_saucal_test' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-saucal-test.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_saucal_test() {

	$plugin = new Saucal_Test();
	$plugin->run();

}
run_saucal_test();

// Add the admin menu item
function omdb_api_add_admin_menu() {
    add_menu_page(
        'Saucal Test Settings',       // Page title
        'OMDB API',                // Menu title
        'manage_options',             // Capability
        'omdb-api-settings',       // Menu slug
        'omdb_api_settings_page',  // Callback function
        'dashicons-format-video',     // Icon URL
        20                            // Position (20 ensures it is among the top level)
    );
}
add_action( 'admin_menu', 'omdb_api_add_admin_menu' );

// Register settings
function omdb_api_register_settings() {
    register_setting( 'omdb_api_settings_group', 'omdb_api_key' );
    register_setting( 'omdb_api_settings_group', 'omdb_api_base_url' );

    add_settings_section(
        'omdb_api_settings_section',
        'OMDB API Settings',
        null,
        'omdb-api-settings'
    );

    add_settings_field(
        'omdb_api_key',
        'API Key',
        'omdb_api_api_key_field_cb',
        'omdb-api-settings',
        'omdb_api_settings_section'
    );

    add_settings_field(
        'omdb_api_base_url',
        'API Base URL',
        'omdb_api_api_base_url_field_cb',
        'omdb-api-settings',
        'omdb_api_settings_section'
    );
}
add_action( 'admin_init', 'omdb_api_register_settings' );

// Callback functions for settings fields
function omdb_api_api_key_field_cb() {
    $api_key = get_option( 'omdb_api_key' );
    echo '<input type="password" name="omdb_api_key" value="' . esc_attr( $api_key ) . '" class="api-input">';
}

function omdb_api_api_base_url_field_cb() {
    $api_base_url = get_option( 'omdb_api_base_url' );
    echo '<input type="text" name="omdb_api_base_url" value="' . esc_attr( $api_base_url ) . '" class="api-input">';
}

// Display the settings page
function omdb_api_settings_page() {
    ?>
    <div class="wrap">
        <h1>OMDB API</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'omdb_api_settings_group' );
            do_settings_sections( 'omdb-api-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

