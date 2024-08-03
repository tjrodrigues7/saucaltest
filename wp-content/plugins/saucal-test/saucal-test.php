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

// Enqueue script and localize data
function saucal_test_enqueue_scripts() {
    wp_enqueue_script('saucal-test-public'); // Ensure the script is enqueued
    wp_localize_script('saucal-test-public', 'wpApiSettings', array(
        'root' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
    ));
}
add_action('wp_enqueue_scripts', 'saucal_test_enqueue_scripts');

// Register REST API methods
function register_omdb_rest_routes() {
    register_rest_route('omdb/v1', '/omdb-search', array(
        'methods' => 'POST',
        'callback' => 'handle_omdb_search',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_omdb_rest_routes');


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
				<form id="omdb-search-form" name="omdb-search" action="" method="post">
					<input name="title" type="text" class="omdb-search-input" placeholder="Title" required>
					<input name="year" type="text" class="omdb-search-input" placeholder="Year">
					<select name="plot" class="omdb-search-input">
						<option value="short" selected>Short plot</option>
						<option value="full">Full plot</option>
					</select>
					<input id="omdb-search-btn" type="submit" value="Search" class="omdb-search-btn">
        			<img class="ajax-loader" src="/wp-content/uploads/2024/08/Fading-circles.gif" width="50" height="18"/>
				</form>
			</div>
			<div id="omdb-results" class="omdb-results">
			
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

//Search content on the OMDB database
function handle_omdb_search($request) {
    $params = $request->get_params();
    $title = sanitize_text_field($params['title']);
    $year = sanitize_text_field($params['year']);
    $plot = sanitize_text_field($params['plot']);
    $user_id = get_current_user_id(); // Get the current user ID

    $omdb_api_key = get_option('omdb_api_key'); // API key
    $omdb_api_base_url = get_option('omdb_api_base_url'); // API endpoint

    if (empty($omdb_api_key) || empty($omdb_api_base_url)) {
        return new WP_REST_Response(['error' => 'API settings are not configured.'], 400);
    }

    //cache key based on user ID and search parameters
    $cache_key = 'omdb_search_' . $user_id . '_' . md5($title . $year . $plot);
    $cached_result = get_transient($cache_key);

    if ($cached_result !== false) {
        return new WP_REST_Response([
            'message' => 'Results from cache',
            'results' => $cached_result,
            'status' => 200,
            'cached' => true
        ], 200);
    }

    //API structure
    $api_url = add_query_arg([
        'apikey' => $omdb_api_key,
        't' => $title,
        'y' => $year,
        'plot' => $plot
    ], $omdb_api_base_url);

    //API request
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return new WP_REST_Response(['error' => $response->get_error_message()], 500);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($data['Response'] === 'False') {
        return new WP_REST_Response(['error' => $data['Error']], 404);
    }

    // Extract api results
    $results = array(
        'Title' => $data['Title'],
        'Year' => $data['Year'],
        'Rated' => $data['Rated'],
        'Released' => $data['Released'],
        'Runtime' => $data['Runtime'],
        'Genre' => $data['Genre'],
        'Director' => $data['Director'],
        'Writer' => $data['Writer'],
        'Actors' => $data['Actors'],
        'Plot' => $data['Plot'],
        'Language' => $data['Language'],
        'Country' => $data['Country'],
        'Awards' => $data['Awards'],
        'Poster' => $data['Poster'],
        'imdbRating' => $data['imdbRating'],
        'Type' => $data['Type'],
        'BoxOffice' => $data['BoxOffice'],
    );

    // Save the result to cache for 4 hour (14400 seconds)
    set_transient($cache_key, $results, 14400);

    return new WP_REST_Response([
        'message' => 'Search completed',
        'results' => $results,
        'status' => 200,
        'cached' => false
    ], 200);
}




