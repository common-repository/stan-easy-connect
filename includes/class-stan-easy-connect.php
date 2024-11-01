<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://compte.stan-app.fr
 * @since      0.1.0
 *
 * @package    Stan_Easy_Connect
 * @subpackage Stan_Easy_Connect/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    Stan_Easy_Connect
 * @subpackage Stan_Easy_Connect/includes
 * @author     Brightweb <jonathan@brightweb.cloud>
 */
class Stan_Easy_Connect {

	/**
	 * API URL
	 */
	const PROD_API_AUTH_URL = 'https://api.stan-app.fr';

	/**
	 * OAuth Client ID
	 */
	private $client_id;

	/**
	 * OAuth Client Secret
	 */
	private $client_secret;

	/**
	 * Display Stan Connect button in login page
	 */
	private $display_in_login;

	/**
	 * OAuth Scope
	 */
	private $scope;

	/**
	 * Redirect URI defines where the user is redirected after login
	 */
	private $redirect_uri;

	/**
	 * Endpoint to initiate a login
	 */
	private $login_endpoint;

	/**
	 * Endpoint to get user infos
	 */
	private $user_endpoint;

	/**
	 * Endpoint to get token
	 */
	private $token_endpoint;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      Stan_Easy_Connect_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $public_plugin    Instanciation of the
	 */
	private $public_plugin;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {
		if ( defined( 'STAN_EASY_CONNECT_VERSION' ) ) {
			$this->version = STAN_EASY_CONNECT_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'stan-easy-connect';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$options = get_option( Stan_Easy_Connect_Admin::$option_name, array() );

		$this->client_id = isset( $options[ 'client_id' ] ) ? $options[ 'client_id' ] : '';
		$this->client_secret = isset( $options[ 'client_secret' ] ) ? $options[ 'client_secret' ] : '';
		// $this->display_in_login = isset( $options[ 'stan_connect_in_login' ] ) ? $options[ 'stan_connect_in_login' ] : false;
		$this->display_in_login = true;
		$this->redirect_uri = site_url( '/stan-easy-connect-authorize' );

		update_option( Stan_Easy_Connect_Admin::$option_name, array(
			'redirect_uri' => $this->redirect_uri,
			'stan_api_auth_url' => Stan_Easy_Connect::GetAPIURL(),
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'stan_connect_in_login' => $this->display_in_login
		) );

		// TODO make it a setting
		$this->scope = 'openid email phone profile address';

		$this->login_endpoint = sprintf( '%s%s', Stan_Easy_Connect::GetAPIURL(), '/v1/oauth/auth' );
		$this->token_endpoint = sprintf( '%s%s', Stan_Easy_Connect::GetAPIURL(), '/v1/oauth/token' );
		$this->user_endpoint = sprintf( '%s%s', Stan_Easy_Connect::GetAPIURL(), '/v1/sessions/users' );

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Stan_Easy_Connect_Loader. Orchestrates the hooks of the plugin.
	 * - Stan_Easy_Connect_i18n. Defines internationalization functionality.
	 * - Stan_Easy_Connect_Wrapper. Handles auth process.
	 * - Stan_Easy_Connect_Button. Handles the stan connect button.
	 * - Stan_Easy_Connect_Admin. Defines all hooks for the admin area.
	 * - Stan_Easy_Connect_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stan-easy-connect-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stan-easy-connect-i18n.php';

		/**
		 * The class handles everything related to auth process.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stan-easy-connect-wrapper.php';

		/**
		 * The class handles the connect button.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stan-easy-connect-button.php';

		/**
		 * The class handles logs.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stan-easy-connect-logger.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-stan-easy-connect-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-stan-easy-connect-public.php';

		$this->loader = new Stan_Easy_Connect_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Stan_Easy_Connect_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Stan_Easy_Connect_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Stan_Easy_Connect_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Stan_Easy_Connect_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.1.0
	 * @return    Stan_Easy_Connect_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Loads classes
	 *
	 * @since 0.1.0
	 */
	public function init() {
		$wrapper = new Stan_Easy_Connect_Wrapper( $this );
		new Stan_Easy_Connect_Button( $wrapper, $this->get_plugin_name(), $this->get_version() );

		add_rewrite_rule( '^stan-easy-connect-authorize/?', 'index.php?stan-easy-connect-authorize=1', 'top' );
		add_rewrite_tag( '%stan-easy-connect-authorize%', '1' );
		add_action( 'parse_request', array( $wrapper, 'parse_callback_request' ) );
		add_filter( 'query_vars', function( $query_vars ) {
			$query_vars[] = 'stan-easy-connect-authorize';
			return $query_vars;
		});

		flush_rewrite_rules();

		if ( $this->display_in_login && ! has_action( 'login_form' ) ) {
			add_action( 'login_form_register', array( $this, 'display_login_stan_connect' ) );
		}

		if ( is_user_logged_in() ) {
			add_action( 'wp_loaded', array( $wrapper, 'verify_token_validity' ) );
		}
	}

	public function display_login_stan_connect() {
		echo do_shortcode( '[stan_easy_connect_button]' );
	}

	/**
	 * Builds a single use authentication URL
	 *
	 * @since 0.1.0
	 * @return string The single use auth URL
	 */
	public function build_authentication_url() {
		$queryParams = sprintf( 'response_type=%1$s&scope=%2$s&client_id=%3$s&state=%4$s&redirect_uri=%5$s',
			'code',
			rawurlencode( $this->scope ),
			$this->client_id,
			rawurlencode( $this->generate_and_cache_state() ),
			rawurlencode( $this->redirect_uri )
		);
		return $this->login_endpoint . '?' . $queryParams;
	}

	/**
	 * Validates the authentication request
	 *
	 * @since 0.1.0
	 * @param $request
	 *
	 * @return array|\WP_Error
	 */
	public function validate_authentication_request( $request ){
		if ( isset( $request['error'] ) ) {
			return new WP_Error( 'unknown-error', 'An unknown error occurred.', $request );
		}

		if ( ! isset( $request['code'] ) ) {
			return new WP_Error( 'missing-code', 'Missing code.', $request );
		}

		if( ! isset( $request['state']) ) {
			return new WP_Error( 'missing-state', 'Missing state.', $request );
		}

		if ( ! $this->validate_state( $request['state'] ) ) {
			return new WP_Error( 'invalid-state', 'The provided state is invalid.', $request );
		}

		return $request;
	}

	/**
	 * Requests a token given the authorization_code
	 *
	 * @param $code is the authorization_code
	 *
	 * @return array request response
	 */
	public function request_authentication_token( $code ) {
		$parsed_url = parse_url( $this->token_endpoint );
		$host = $parsed_url[ 'host' ];

		$request = array(
			'body' => array(
				'code'          => sanitize_text_field( $code ),
				'client_id'     => $this->client_id,
				'client_secret' => $this->client_secret,
				'redirect_uri'  => $this->redirect_uri,
				'grant_type'    => 'authorization_code',
				'scope'         => $this->scope,
			),
			'headers' => array( 'Host' => $host )
		);

		$response = wp_remote_post( $this->token_endpoint, $request );

		if ( is_wp_error( $response ) ){
			$response->add( 'request_authentication_token' , 'Server error after requesting for authentication token.' );
		}

		return $response;
	}

	/**
	 * Requests a new token with refresh token
	 *
	 * @since 0.1.0
	 * @param $refresh_token
	 *
	 * @return array request response
	 */
	public function request_new_tokens( $refresh_token ) {
		$request = array(
			'body' => array(
				'refresh_token' => $refresh_token,
				'client_id'     => $this->client_id,
				'client_secret' => $this->client_secret,
				'grant_type'    => 'refresh_token'
			)
		);

		$response = wp_remote_post( $this->endpoint_token, $request );

		if ( is_wp_error( $response ) ) {
			$response->add( 'refresh_token' , 'Server error after requesting for authentication refresh token.' );
		}

		return $response;
	}

	/**
	 * Gets and parse the token response
	 *
	 * @since 0.1.0
	 * @param $token_res
	 *
	 * @return array|mixed|object
	 */
	public function get_token_response( $token_res ) {
		if ( ! isset( $token_res[ 'body' ] ) ){
			return new WP_Error( 'missing-token-body', 'The token body is missing.', $token_res );
		}

		$token_res = json_decode( $token_res[ 'body' ], true );

		if ( isset( $token_res[ 'error' ] ) ) {
			$error_desc = $token_res[ 'error_description' ] || $token_res[ 'error' ];
			return new WP_Error( $token_res[ 'error' ], $error_desc, $token_res );
		}

		return $token_res;
	}

	/**
	 * Exchanges an access_token for a user_claim from the user endpoint
	 *
	 * @since 0.1.0
	 * @param $access_token
	 *
	 * @return array
	 */
	public function request_user( $access_token ) {
		$request = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token
			)
		);

		// Host is used to handles the case when the server is behind a proxy
		$parsed_url = parse_url( $this->user_endpoint );
		$host = $parsed_url[ 'host' ];

		if ( !empty( $parsed_url[ 'port' ] ) ) {
			$host .= ":{$parsed_url['port']}";
		}

		$request[ 'headers' ][ 'Host' ] = $host;

		$response = wp_remote_post( $this->user_endpoint, $request );

		if ( is_wp_error( $response ) ){
			$response->add( 'request_user' , 'Request user failed.' );
		}

		return $response;
	}

	/**
	 * Exchanges a token with claim
	 *
	 * @since 0.1.0
	 * @param $token_response
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	public function get_user_claim( $token_response ){
		$user_claim_result = $this->request_user( $token_response[ 'access_token' ] );

		if ( is_wp_error( $user_claim_result ) || ! isset( $user_claim_result[ 'body' ] ) ) {
			return new WP_Error( 'bad-claim', 'Claim invalid or body is missing from claim.', $user_claim_result );
		}

		$user_claim = json_decode( $user_claim_result[ 'body' ], true );

		return $user_claim;
	}

	/**
	 * Gets the authorization code from the request
	 *
	 * @since 0.1.0
	 * @param $request array
	 *
	 * @return string
	 */
	public function get_authentication_code( $request ){
		return $request[ 'code' ];
	}

	/**
	 * Generates a MD5 state stored in transient (cache)
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function generate_and_cache_state() {
		$state = md5( mt_rand() . microtime( true ) );

		// last for 10 minutes
		set_transient( 'stan-easy-connect-state--' . $state, $state, 10 * 60 );
		return $state;
	}

	/**
	 * Validates a state from cache.
	 *
	 * @since 0.1.0
	 * @param $state
	 *
	 * @return bool
	 */
	public function validate_state( $state ) {
		$transient_state = get_transient( 'stan-easy-connect-state--' . $state );
		return !!$transient_state;
	}

	/**
	 * Validates a bearer token response
	 *
	 * @param $token_response
	 *
	 * @return bool|\WP_Error
	 */
	public function validate_token_response( $token_response ){
		if ( ! isset( $token_response[ 'id_token' ] ) ||
		     ! isset( $token_response[ 'token_type' ] ) || strcasecmp( $token_response[ 'token_type' ], 'bearer' )
		) {
			return new WP_Error( 'invalid-token-response', 'The token is invalid, id_token and token_type might be missing, or token_type is not Bearer.', $token_response );
		}
		return true;
	}

	/**
	 * Validates id_token_claim
	 *
	 * @since 0.1.0
	 * @param $id_token_claim
	 *
	 * @return bool|\WP_Error
	 */
	public function validate_id_token_claim( $id_token_claim ){
		if ( ! is_array( $id_token_claim ) ) {
			return new WP_Error( 'bad-id-token-claim', 'Token claim ID is invalid.', $id_token_claim );
		}

		if ( ! isset( $id_token_claim[ 'sub' ] ) || empty( $id_token_claim[ 'sub' ] ) ) {
			return new WP_Error( 'missing-sub', 'Subject Identity is missing from token claim.', $id_token_claim );
		}

		return true;
	}

	/**
	 * Validates user claim.
	 *
	 * @since 0.1.0.
	 * @param $user_claim
	 * @param $id_token_claim
	 *
	 * @return boolean|\WP_Error
	 */
	public function validate_user_claim( $user_claim, $id_token_claim ) {
		if ( ! is_array( $user_claim ) ){
			return new WP_Error( 'invalid-user-claim', 'user_claim must be an array.', $user_claim );
		}

		if ( isset( $user_claim['error'] ) ) {
			$message = $user_claim[ 'error_description' ] || $user_claim[ 'error' ];
			return new WP_Error( 'invalid-user-claim-' . $user_claim['error'], $message, $user_claim );
		}

		if ( $id_token_claim[ 'sub' ] !== $user_claim[ 'sub' ] ) {
			return new WP_Error( 'incorrect-user-claim', 'This user claim is not expected.', func_get_args() );
		}

		return true;
	}

	/**
	 * Gets id_token_claim from token response
	 *
	 * @since 0.1.0
	 * @param $token_response
	 *
	 * @return array|\WP_Error
	 */
	public function get_id_token_claim( $token_response ){
		if ( ! isset( $token_response['id_token'] ) ) {
			return new WP_Error( 'missing-id-token', 'id_token is missing.', $token_response );
		}

		$tmp = explode( '.', $token_response['id_token'] );

		if ( ! isset( $tmp[1] ) ) {
			return new WP_Error( 'missing-identity', 'Missing identity.', $token_response );
		}

		$id_token_claim = json_decode(
			base64_decode(
				str_replace(
					array('-', '_'),
					array('+', '/'),
					$tmp[1]
				)
			)
			, true
		);

		return $id_token_claim;
	}

	/**
	 * Gets the subject identity from the id_token
	 *
	 * @since 0.1.0
	 * @param $id_token_claim array
	 *
	 * @return mixed
	 */
	public function get_subject_identity( $id_token_claim ) {
		return $id_token_claim[ 'sub' ];
	}

	/**
	 * Returns the good endpoint.
	 *
	 * @since 1.2.0
	 * @return string
	 * @access private
	 */
	public static function GetAPIURL() {
		return self::PROD_API_AUTH_URL;
	}
}
