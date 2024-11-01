<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://compte.stan-app.fr
 * @since      0.1.0
 *
 * @package    Stan_Easy_Connect
 * @subpackage Stan_Easy_Connect/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.1.0
 * @package    Stan_Easy_Connect
 * @subpackage Stan_Easy_Connect/includes
 * @author     Brightweb <jonathan@brightweb.cloud>
 */
class Stan_Easy_Connect_Deactivator {

	/**
	 * Do something when the plugin is deactivated
	 *
	 *
	 * @since    0.1.0
	 */
	public static function deactivate() {
		$url = 'https://account.stan-app.fr/account/pkcg94c5ggj9n4aycr7gnvnmhrkctr/integrations/notify';

		$body = array(
			'website' => site_url(),
			'source' => 'stan-connect',
			'stack' => 'wordpress',
			'is_active' => false
		);

		$headers = array(
			'Content-Type' => 'application/json',
			'Authorization' => 'ApiKey xjGc42kfJxTZtR4KGeBUnN4H34V5HwBa3U'
		);

		wp_remote_post( $url, array(
			'body' => json_encode( $body ),
			'headers' => $headers
		));
	}

}
