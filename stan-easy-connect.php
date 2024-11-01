<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://compte.stan-app.fr
 * @since             0.1.0
 * @package           Stan_Easy_Connect
 *
 * @wordpress-plugin
 * Plugin Name:       Stan Easy Connect
 * Plugin URI:        https://compte.stan-app.fr
 * Description:       Vous perdez des utilisateurs lorsque vous demandez de s'inscrire, remplir les formulaires est la première raison qui mène les utilisateurs à quitter un site. Avec Stan Connect vos utilisateurs s'inscrivent sans formulaire, sans contrainte.
 * Version:           1.4.8
 * Author:            Brightweb
 * Author URI:        https://brightweb.cloud
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       stan-easy-connect
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'STAN_EASY_CONNECT_VERSION', '1.4.4' );
define( 'WC_STAN_CONNECT_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-stan-easy-connect-activator.php
 */
function activate_stan_easy_connect() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-stan-easy-connect-activator.php';
	Stan_Easy_Connect_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-stan-easy-connect-deactivator.php
 */
function deactivate_stan_easy_connect() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-stan-easy-connect-deactivator.php';
	Stan_Easy_Connect_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_stan_easy_connect' );
register_deactivation_hook( __FILE__, 'deactivate_stan_easy_connect' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-stan-easy-connect.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_stan_easy_connect() {

	$plugin = new Stan_Easy_Connect();
	$plugin->run();

}
run_stan_easy_connect();

add_filter( 'plugin_action_links_stan-easy-connect/stan-easy-connect.php', 'display_stan_connect_settings_link' );
add_filter( 'plugin_action_links_wp-stan-easy-connect/stan-easy-connect.php', 'display_stan_connect_settings_link' );
function display_stan_connect_settings_link( $links ) {
	$url = get_stan_connect_settings_link();

	$settings_link = "<a href='$url'>Configurer</a>";

	array_push(
		$links,
		$settings_link
	);
	return $links;
}

function get_stan_connect_settings_link() {
	return esc_url( add_query_arg(
		array(
			'page' => 'stan-easy-connect'
		),
		get_admin_url() . 'options-general.php'
	) );
}
