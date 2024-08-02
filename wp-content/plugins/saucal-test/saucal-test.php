<?php

/**
 * Plugin Name:       Saucal Test
 * Plugin URI:        https://www.linkedin.com/in/ivan-rodrigues7/
 * Description:       A custom plugin for the Saucal test with OMDB API settings and WooCommerce custom tab.
 * Version:           1.0.0
 * Author:            Ivan Rodrigues
 * Author URI:        https://www.linkedin.com/in/ivan-rodrigues7/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       saucal-test
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add the OMDB API menu item.
 */
function omdb_api_add_admin_menu() {
    add_menu_page(
        'Saucal Test Settings', 
        'OMDB API',          
        'manage_options',           
        'omdb-api-settings',       
        'omdb_api_settings_page',  
        'dashicons-format-video',     
        20                        
    );
}
add_action( 'admin_menu', 'omdb_api_add_admin_menu' );

/**
 * Register OMDB API settings.
 */
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

/**
 * Callback function for API Key field.
 */
function omdb_api_api_key_field_cb() {
    $api_key = get_option( 'omdb_api_key' );
    echo '<input type="password" name="omdb_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text">';
}

/**
 * Callback function for API Base URL field.
 */
function omdb_api_api_base_url_field_cb() {
    $api_base_url = get_option( 'omdb_api_base_url' );
    echo '<input type="text" name="omdb_api_base_url" value="' . esc_attr( $api_base_url ) . '" class="regular-text">';
}

/**
 * Display the OMDB API settings page.
 */
function omdb_api_settings_page() {
    ?>
    <div class="wrap">
        <h1>OMDB API Settings</h1>
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

/**
 * Add a custom tab to the WooCommerce My Account menu.
 *
 * @param array $items Existing menu items.
 * @return array Modified menu items.
 */
function custom_my_account_add_tab( $items ) {
    $items['omdb_tab'] = __('OMDB Tab', 'saucal-test');
    return $items;
}
add_filter('woocommerce_account_menu_items', 'custom_my_account_add_tab');

/**
 * Add the endpoint for the custom tab.
 */
function omdb_tab_add_my_account_endpoint() {
    add_rewrite_endpoint('omdb_tab', EP_ROOT | EP_PAGES);
}
add_action('init', 'omdb_tab_add_my_account_endpoint');

/**
 * Display the content for the custom tab using a custom template.
 */
function omdb_tab_my_account_endpoint_content() {
    $template = plugin_dir_path(__FILE__) . 'templates/omdb-tab-template.php';
    
    if ( file_exists($template) ) {
        include $template;
    } else {
        echo '<p>' . __('Content not found.', 'saucal-test') . '</p>';
    }
}
add_action('woocommerce_account_omdb_tab_endpoint', 'omdb_tab_my_account_endpoint_content');

/**
 * Ensure that the new endpoint is flushed on plugin activation.
 */
function omdb_tab_flush_rewrite_rules() {
    omdb_tab_add_my_account_endpoint();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'omdb_tab_flush_rewrite_rules');

/**
 * Ensure that the endpoint is removed on plugin deactivation.
 */
function omdb_tab_remove_rewrite_rules() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'omdb_tab_remove_rewrite_rules');
