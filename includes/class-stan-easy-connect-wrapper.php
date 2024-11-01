<?php

class Stan_Easy_Connect_Wrapper {

	/**
	 * Key of the token refresh key in cookie
	 */
	public static $cookie_token_refresh_key = 'stan-easy-connect-refresh';

	/**
	 * Key of the redirect key in cookie
	 */
	public static $cookie_redirect_key = 'stan-easy-connect-redirect';

	/**
	 * Stan connect client
	 */
	private $client;

	/**
	 * Inject necessary objects and services into the client
	 *
	 * @since 0.1.0
	 * @param \Stan_Easy_Connect $client
	 */
	public function __construct( Stan_Easy_Connect $client ){
		$this->client = $client;
	}

	/**
	 * Authenticates, authorizes and login / signup the user
	 *
	 * @since 0.1.0
	 */
	public function authentication_request_callback() {
		$client = $this->client;

		/**
		 * Authentication
		 */
		$authentication_request = $client->validate_authentication_request( $_GET );

		if ( is_wp_error( $authentication_request ) ){
			$this->error_redirect( $authentication_request );
		}

		$code = $client->get_authentication_code( $authentication_request );

		if ( is_wp_error( $code ) ){
			$this->error_redirect( $code );
		}

		$token_result = $client->request_authentication_token( $code );

		if ( is_wp_error( $token_result ) ) {
			$this->error_redirect( $token_result );
		}

		$token_response = $client->get_token_response( $token_result );

		if ( is_wp_error( $token_response ) ){
			$this->error_redirect( $token_response );
		}

		$valid = $client->validate_token_response( $token_response );

		if ( is_wp_error( $valid ) ) {
			$this->error_redirect( $valid );
		}

		/**
		 * Authorization
		 */
		$id_token_claim = $client->get_id_token_claim( $token_response );

		if ( is_wp_error( $id_token_claim ) ){
			$this->error_redirect( $id_token_claim );
		}

		$valid = $client->validate_id_token_claim( $id_token_claim );

		if ( is_wp_error( $valid ) ){
			$this->error_redirect( $valid );
		}

		$user_claim = $client->get_user_claim( $token_response );

		if ( is_wp_error( $user_claim ) ){
			$this->error_redirect( $user_claim );
		}

		$valid = $client->validate_user_claim( $user_claim, $id_token_claim );

		if ( is_wp_error( $valid ) ){
			$this->error_redirect( $valid );
		}

		/**
		 * Fetch user
		 */
		$subject_identity = $client->get_subject_identity( $id_token_claim );
		$user = $this->get_user_by_sub( $subject_identity );

		if ( ! $user ) {
			$user = $this->create_new_user( $subject_identity, $user_claim );
			if ( is_wp_error( $user ) ) {
				$this->error_redirect( $user );
			}
		} else {
			$uid = $this->find_user_id( $user_claim[ 'email' ] );

			if ( $uid ) {
				$this->update_user_address( $uid, $user_claim[ 'shipping_address' ], $user_claim[ 'given_name' ], $user_claim[ 'family_name' ] );
			}
		}

		$valid = $this->validate_user( $user );

		if ( is_wp_error( $valid ) ){
			$this->error_redirect( $valid );
		}

		$this->login_user( $user, $token_response, $id_token_claim, $user_claim, $subject_identity  );

		$redirect_url = isset( $_COOKIE[ self::$cookie_redirect_key ] ) ? esc_url_raw( $_COOKIE[ self::$cookie_redirect_key ] ) : false;

		if( !empty( $redirect_url ) ) {
			setcookie( self::$cookie_redirect_key, $redirect_url, 1, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
			wp_redirect( $redirect_url );
		} else {
			if ( Stan_Easy_Connect_Admin::is_woocommerce_installed() && ! WC()->cart->is_empty() ) {
				wp_redirect( wc_get_checkout_url() );
				exit;
			}
			wp_redirect( home_url() );
		}

		exit;
	}

	/**
	 * Records user meta data, and provide an authorization cookie
	 *
	 * @since 0.1.0
	 * @param $user
	 */
	function login_user( $user, $token_response, $id_token_claim, $user_claim, $subject_identity ) {
		update_user_meta( $user->ID, 'stan-easy-connect-last-token-response', $token_response );
		update_user_meta( $user->ID, 'stan-easy-connect-last-id-token-claim', $id_token_claim );
		update_user_meta( $user->ID, 'stan-easy-connect-last-user-claim', $user_claim );

		$expiration = time() + apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS, $user->ID, false );
		$manager = WP_Session_Tokens::get_instance( $user->ID );
		$token = $manager->create( $expiration );

		Stan_Easy_Connect_Admin::$logger->log( "Loggin user: {$user->user_login} has ID: {$user->ID}", 'login-user' );

		$this->save_refresh_token( $manager, $token, $token_response );

		wp_set_auth_cookie( $user->ID, false, '', $token);
		do_action( 'wp_login', $user->user_login, $user );

		if ( class_exists( 'woocommerce' ) ) {
			// Applies Stanner coupon
			$cart = WC()->cart;
			if ( $cart ) {
				$coupon = new WC_Coupon( 'STANNER' );
				if ( ! $coupon->is_valid() ) {
					Stan_Easy_Connect_Admin::$logger->log( "Stan Connect a tenté d'appliquer le coupon {$coupon->get_code()}, ce coupon est invalide" );
					return;
				}
				$cart->apply_coupon( $coupon->get_code() );
			}
		}
	}

	/**
	 * Save refresh token to session tokens
	 *
	 * @since 0.1.0
	 *
	 * @param $manager
	 * @param $token
	 * @param $token_response
	 */
	function save_refresh_token( $manager, $token, $token_response ) {
		$session = $manager->get( $token );

		$now = current_time( 'timestamp' , true );
		$session[ self::$cookie_token_refresh_key ] = array(
			'next_access_token_refresh_time' => $token_response[ 'expires_in' ] + $now,
			'refresh_token' => isset( $token_response[ 'refresh_token' ] ) ? $token_response[ 'refresh_token' ] : false,
			'refresh_expires' => false,
		);

		if ( isset( $token_response[ 'refresh_expires_in' ] ) ) {
			$refresh_expires_in = $token_response[ 'refresh_expires_in' ];
			if ($refresh_expires_in > 0) {
				$refresh_expires = $now + $refresh_expires_in - 5;
				$session[ self::$cookie_token_refresh_key ][ 'refresh_expires' ] = $refresh_expires;
			}
		}

		$manager->update( $token, $session );
	}

	/**
	 * Gets a auth URL
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_authentication_url(){
		return $this->client->build_authentication_url();
	}

	/**
	 * Tries to find existing user with Stan sub
	 *
	 * @param $subject_identity
	 *
	 * @return false|\WP_User
	 */
	public function get_user_by_sub( $subject_identity ){
		$user_query = new WP_User_Query( array(
			'meta_query' => array(
				array(
					'key'   => 'stan-easy-connect-sub',
					'value' => $subject_identity,
				)
			)
		) );

		if ( $user_query->get_total() > 0 ) {
			$users = $user_query->get_results();
			return $users[0];
		}

		return false;
	}

	/**
	 * Create a new user with user_claims
	 *
	 * @since 0.1.0
	 * @param $subject_identity
	 * @param $user_claim
	 *
	 * @return \WP_Error | \WP_User
	 */
	function create_new_user( $subject_identity, $user_claim ) {
		if ( ! isset( $user_claim[ 'email' ] ) ) {
			Stan_Easy_Connect_Admin::$logger->log( "Failed to retrieve claim because email is missing", 'fail-retrieve-user' );
			return new WP_Error( 'fail-retrieve-user', 'Email is missing, please set one in Stan' );
		}

		$email = $user_claim[ 'email' ];

		$username = $email;
		$email    = $email;
		$nickname = $user_claim[ 'given_name' ];
		$displayname = $user_claim[ 'given_name' ];;

		$values_missing = false;

		$uid = $this->find_user_id( $username );

		if ( $uid ) {
			$user = $this->update_existing_user( $uid, $subject_identity, $user_claim[ 'shipping_address' ] );
			return $user;
		}

		$is_woocommerce_installed = Stan_Easy_Connect_Admin::is_woocommerce_installed();

		$firstname = isset( $user_claim[ 'given_name' ] ) ? $user_claim[ 'given_name' ] : '';
		$lastname = isset( $user_claim[ 'family_name' ] ) ? $user_claim[ 'family_name' ] : '';

		$user_data = array(
			'user_login' => $username,
			'user_pass' => wp_generate_password( 32, true, true ),
			'user_email' => $email,
			'display_name' => $displayname,
			'nickname' => $nickname,
			'first_name' => $firstname,
			'last_name' => $lastname,
			'role' => $is_woocommerce_installed ? 'customer' : 'subscriber'
		);

		$uid = wp_insert_user( $user_data );

		if ( isset( $user_claim[ 'phone' ] ) ) {
			update_user_meta( $uid, 'mobile', $user_claim[ 'phone' ] );
			update_user_meta( $uid, 'billing_phone', $user_claim[ 'phone' ] );
			update_user_meta( $uid, 'shipping_phone', $user_claim[ 'phone' ] );
		}

		$this->update_user_address( $uid, $user_claim[ 'shipping_address' ], $firstname, $lastname );

		if ( is_wp_error( $uid ) ) {
			Stan_Easy_Connect_Admin::$logger->log( "Failed to create user: {$uid}", 'fail-create-user' );
			return new WP_Error( 'failed-user-creation', 'Failed user creation.', $uid );
		}

		$user = get_user_by( 'id', $uid );

		add_user_meta( $user->ID, 'stan-easy-connect-subject-identity', (string) $subject_identity, true );

		Stan_Easy_Connect_Admin::$logger->log( "New user created: {$user->user_login} ($uid)", 'success-create-user' );

		return $user;
	}

	/**
	 * Update an existing user with OpenID Connect meta data
	 *
	 * @since 1.0.3
	 * @param $uid
	 * @param $address
	 *
	 */
	function update_user_address( $uid, $address, $default_firstname = '', $default_lastname = '' ) {
		if ( Stan_Easy_Connect_Admin::is_woocommerce_installed() && $address ) {
			update_user_meta( $uid, 'billing_first_name', isset( $address['firstname'] ) ? $address['firstname'] : $default_firstname );
			update_user_meta( $uid, 'billing_last_name', isset( $address['lastname'] ) ? $address['lastname'] : $default_lastname );
			update_user_meta( $uid, 'billing_address_1', $address['street_address']);
			update_user_meta( $uid, 'billing_address_2', $address['street_address_line2']);
			update_user_meta( $uid, 'billing_city', $address['locality']);
			update_user_meta( $uid, 'billing_postcode', $address['zip_code']);
			// update_user_meta( $uid, 'billing_country', $address['country'] ? $address['country'] : 'France');
			update_user_meta( $uid, 'billing_country', 'FR');
			update_user_meta( $uid, 'billing_state', $address['region']);

			update_user_meta( $uid, 'shipping_first_name', $address['firstname']);
			update_user_meta( $uid, 'shipping_last_name', $address['lastname']);
			update_user_meta( $uid, 'shipping_address_1', $address['street_address']);
			update_user_meta( $uid, 'shipping_address_2', $address['street_address_line2']);
			update_user_meta( $uid, 'shipping_city', $address['locality']);
			update_user_meta( $uid, 'shipping_postcode', $address['zip_code']);
			// update_user_meta( $uid, 'shipping_country', $address['country'] ? $address['country'] : 'France');
			update_user_meta( $uid, 'shipping_country', 'FR');
			update_user_meta( $uid, 'shipping_state', $address['region']);
		}
	}

	/**
	 * Update an existing user with OpenID Connect meta data
	 *
	 * @since 0.1.0
	 * @param $uid
	 * @param $subject_identity
	 *
	 * @return \WP_Error | \WP_User
	 */
	function update_existing_user( $uid, $subject_identity, $address ) {
		update_user_meta( $uid, 'stan-easy-connect-subject-identity', (string) $subject_identity );

		$usermeta = get_user_meta( $uid );
		$this->update_user_address( $uid, $address, $usermeta[ 'first_name' ][0], $usermeta[ 'last_name' ][0] );

		Stan_Easy_Connect_Admin::$logger->log( "Update user $uid with sub $subject_identity", 'update-user' );

		return get_user_by( 'id', $uid );
	}

	/**
	 * Parse callback request
	 *
	 * @since 0.1.0
	 * @param $query
	 *
	 * @return mixed
	 */
	function parse_callback_request( $query ){
		if ( isset( $query->query_vars[ 'stan-easy-connect-authorize' ] ) &&
		     $query->query_vars[ 'stan-easy-connect-authorize' ] === '1' )
		{
			$this->authentication_request_callback();
			exit;
		}

		return $query;
	}

	/**
	 * Verify token validity
	 *
	 * @since 0.1.0
	 */
	function verify_token_validity() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id = wp_get_current_user()->ID;
		$manager = WP_Session_Tokens::get_instance( $user_id );
		$token = wp_get_session_token();
		$session = $manager->get( $token );

		if ( ! isset( $session[ self::$cookie_token_refresh_key ] ) ) {
			return;
		}

		$current_time = current_time( 'timestamp', true );
		$refresh_token_info = $session[ self::$cookie_token_refresh_key ];

		$next_access_token_refresh_time = $refresh_token_info[ 'next_access_token_refresh_time' ];

		if ( $current_time < $next_access_token_refresh_time ) {
			return;
		}

		$refresh_token = $refresh_token_info[ 'refresh_token' ];
		$refresh_expires = $refresh_token_info[ 'refresh_expires' ];

		if ( ! $refresh_token || ( $refresh_expires && $current_time > $refresh_expires ) ) {
			wp_logout();
			return;
		}

		$token_result = $this->client->request_new_tokens( $refresh_token );

		if ( is_wp_error( $token_result ) ) {
			wp_logout();
			$this->error_redirect( $token_result );
		}

		$token_response = $this->client->get_token_response( $token_result );

		if ( is_wp_error( $token_response ) ) {
			wp_logout();
			$this->error_redirect( $token_response );
		}

		$this->save_refresh_token( $manager, $token, $token_response );
	}

	/**
	 * Validates the WP_User
	 *
	 * @since 0.1.0
	 * @param $user
	 *
	 * @return true|\WP_Error
	 */
	private function validate_user( $user ){
		if ( ! is_a( $user, 'WP_User' ) || ! $user->exists() ) {
			Stan_Easy_Connect_Admin::$logger->log( "Invalid user: {$user}.", 'user-invalid' );
			return new WP_Error( 'invalid-user', 'The user is invalid.', $user );
		}
		return true;
	}

	/**
	 * Redirects to error page
	 *
	 * @param $error WP_Error
	 */
	private function error_redirect( $error ) {
		Stan_Easy_Connect_Admin::$logger->log( $error, 'error-redirect' );

		$redirect_url = wp_login_url();

		if ( Stan_Easy_Connect_Admin::is_woocommerce_installed() && ! WC()->cart->is_empty() ) {
			$redirect_url = wc_get_checkout_url();
		}

		wp_redirect(
			$redirect_url .
			'?login-error=' . $error->get_error_code() .
			'&message=' . urlencode( $error->get_error_message() )
		);

		exit;
	}

	/**
	 * Redirects to error page
	 * @since 1.0.3
	 *
	 * @param $email user's email
	 * @return $uid | null
	 */
	private function find_user_id( $email ) {
		$uid = username_exists( $email );
		if ( ! $uid ) {
			$uid = email_exists( $email );
		}
		return $uid;
	}
}
