<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://compte.stan-app.fr
 * @since      0.1.0
 *
 * @package    Stan_Easy_Connect
 * @subpackage Stan_Easy_Connect/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.1.0
 * @package    Stan_Easy_Connect
 * @subpackage Stan_Easy_Connect/includes
 * @author     Brightweb <jonathan@brightweb.cloud>
 */
class Stan_Easy_Connect_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'stan-easy-connect',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
