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
	omdb_tab_add_my_account_endpoint();
    flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-saucal-test-deactivator.php
 */
function deactivate_saucal_test() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-saucal-test-deactivator.php';
	Saucal_Test_Deactivator::deactivate();
	flush_rewrite_rules();
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

// Add the OMDB API menu item
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

// OMDB API register settings
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

// Callback functions for OMDB API settings fields
function omdb_api_api_key_field_cb() {
    $api_key = get_option( 'omdb_api_key' );
    echo '<input type="password" name="omdb_api_key" value="' . esc_attr( $api_key ) . '" class="api-input">';
}

function omdb_api_api_base_url_field_cb() {
    $api_base_url = get_option( 'omdb_api_base_url' );
    echo '<input type="text" name="omdb_api_base_url" value="' . esc_attr( $api_base_url ) . '" class="api-input">';
}

// Display the OMDB API settings page
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

/**
 * Add the OMDB tab to  My Account menu.
 *
 * @param array $items Existing menu items.
 * @return array Modified menu items.
 */
function custom_my_account_add_tab( $items ) {
	$new_tab = array( 'omdb_tab' => __('OMDB Tab', 'saucal-test') );    

	 $logout_link = array_pop($items);
	 $items = array_merge($items, $new_tab);
	 $items['logout'] = $logout_link;
 
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
 * Display the content from a custom template page.
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

// Register OMDB widget area
function omdb_tab_widget_area() {
    register_sidebar( array(
        'name'          => __( 'OMDB Tab Area', 'saucal-test' ),
        'id'            => 'omdb-tab-area',
        'description'   => __( 'A custom widget area for the OMDB tab.', 'saucal-test' ),
        'before_widget' => '<div class="omdb-tab-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="omdb-tab-widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'omdb_tab_widget_area' );

// Register the search widget
function custom_register_omdb_search_widget() {
    register_widget( 'OMDB_Search_Widget' );
}
add_action( 'widgets_init', 'custom_register_omdb_search_widget' );

// Define the custom widget class
class OMDB_Search_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'OMDB_Search_Widget', 
            'OMDB Search Widget', 
            array( 'description' => __( 'A OMDB search widget', 'text_domain' ), )
        );
    }

    // Frontend display of widget
    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        // Output the widget content
        echo'<div class="omdb-search">
				<input id="title" type="text" class="omdb-search-input" placeholder="Title">
				<input id="year" type="text" class="omdb-search-input" placeholder="Year">
				<select id="plot" class="omdb-search-input">
					<option value="short" selected>Short</option>
					<option value="full">Full</option>
				</select>
				<button id="search" class="omdb-search-btn">Search</button>
			</div>';

        echo $args['after_widget'];
    }

    // Backend widget form
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' ); ?>
        <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php 
    }

    // Sanitize widget form values as they are saved
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }
}




