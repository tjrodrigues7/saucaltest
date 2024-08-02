<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://www.linkedin.com/in/ivan-rodrigues7/
 * @since      1.0.0
 *
 * @package    Saucal_Test
 * @subpackage Saucal_Test/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Saucal_Test
 * @subpackage Saucal_Test/includes
 * @author     Ivan Rodrigues <contact@ivanrodrigues.info>
 */
class Saucal_Test_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'saucal-test',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
