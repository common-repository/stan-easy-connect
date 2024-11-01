<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://compte.stan-app.fr
 * @since      0.1.0
 *
 * @package    Stan_Easy_Connect
 * @subpackage Stan_Easy_Connect/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 *
 * @package    Stan_Easy_Connect
 * @subpackage Stan_Easy_Connect/admin
 * @author     Brightweb <jonathan@brightweb.cloud>
 */
class Stan_Easy_Connect_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The fields presented in setting page
	 *
	 * @since 0.1.0
	 * @access private
	 * @var array
	 */
	private $settings_fields;

	/**
	 * Defines settings option name
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public static $option_name = 'stan_easy_connect_settings';

	/**
	 * Logger's instance
	 *
	 * @since 0.1.0
	 * @var \Stan_Easy_Connect_Logger
	 */
	public static $logger;

	/**
	 * Defines the button short code
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public static $short_code = 'stan_easy_connect_button';

	/**
	 * Handles all options
	 *
	 * @since 0.1.0
	 * @var array
	 */
	private $options;

	/**
	 * Returns true if woocommerce is installed
	 *
	 * @since 0.1.0
	 *
	 * @return boolean
	 */
	public static function is_woocommerce_installed() {
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$fields = array(
			'client_id' => array(
				'title'       => 'Identifiant du client Stan Connect',
				'description' => "Il s'agit de votre identifiant de client Stan Connect. <a href='https://compte.stan-app.fr' target='_blank'>Obtenir un identifiant client</a>.",
				'type'        => 'text',
				'section'     => 'client_settings_group',
				'disabled' 	  => true
			),
			'client_secret' => array(
				'title' => 'Client secret',
				'type' => 'text',
				'section' => 'client_settings_group'
			),
			'stan_connect_in_login' => array(
				'title' => 'Se Connecter avec Stan Connect',
				'description' => 'Afficher le bouton Stan Connect sur la page de connexion',
				'type' => 'checkbox',
				'section' => 'client_settings_group'
			),
			'redirect_uri' => array(
				'type' => 'hidden',
				'section' => 'client_settings_group'
			),
			'stan_api_auth_url' => array(
				'type' => 'hidden',
				'id' => 'stan-api-auth-url',
				'section' => 'client_settings_group'
			),
			'test_connection' => array(
				'title' => 'Tester la connexion',
				'description' => 'Tester la connexion avec votre identifiants.',
				'type' => 'button',
				'section' => 'client_settings_group'
			)
		);

		foreach ( $fields as $key => &$field ) {
			$field['key']  = $key;
			$field['name'] = self::$option_name . '[' . $key . ']';
		}

		$this->settings_fields = apply_filters( 'stan-easy-connect-settings-fields', $fields );

		add_action( 'admin_menu', array( $this, 'init_menu_page' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );

		self::$logger = new Stan_Easy_Connect_Logger();
	}

	/**
	 * Initiates the menu button to configure Stan Connect
	 *
	 * @since    0.1.0
	 */
	public function init_menu_page() {
		add_options_page( 'Configurer Stan Connect', ' Stan Easy Connect', 'manage_options', 'stan-easy-connect',
			array( $this, 'render_options_page' ) );
	}

	/**
	 * Initiates the Stan Connect settings page
	 *
	 * @since    0.1.0
	 */
	public function render_options_page() {
		$this->options = get_option( self::$option_name, array() );
		?>
			<div class="stan-connect-settings-header">
				<img class="stan-logo" src="https://compte.stan-app.fr/static/stan-logo.png" alt="Logo de Stan" />
				<p class="description">
					Avec Stan Connect fini les formulaires. Ne perdez plus vos utilisateurs à cause des formulaires de connexion
				</p>
			</div>
			<div class="stan-connect-settings">
				<form method="post" action="options.php">
					<?php settings_fields( 'client_settings_group' ); ?>
					<?php do_settings_sections( 'stan-easy-connect' ); ?>
					<?php submit_button( 'Enregistrer' ); ?>
				</form>

				<p class="description">
					<strong>Le Short code d'intégration du bouton "Se Connecter avec Stan" est </strong>
					<code>[<?php echo self::$short_code; ?>]</code>
				</p>
				<i>Le bouton "Se Connecter avec Stan" est automatiquement intégré dans votre page de login</i>

				<p>Besoin d'aide ? Venez discuter avec notre équipe sur <a href="https://compte.stan-app.fr" target="_blank">Stan App</a></p>
			</div>

			<hr />

			<div class="logs-container">
				<h2>Journal d'activité</h2>&nbsp;&nbsp;<span class="link">[<a href="#" id="display-logs-btn">Afficher</a>]</span>
			</div>
			<div id="logger-table-wrapper">
				<?php print self::$logger->get_logs_table( array() ); ?>
			</div>
		<?php
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Stan_Easy_Connect_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Stan_Easy_Connect_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/stan-easy-connect-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, WP_PLUGIN_DIR . 'public/css/stan-easy-connect-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Stan_Easy_Connect_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Stan_Easy_Connect_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/stan-easy-connect-admin.js', array( 'jquery' ), $this->version, 'all' );

	}

	/**
	 * Resisters Stan Easy Connect settings
	 */
	public function init_settings() {
		register_setting( 'client_settings_group', self::$option_name );

		add_settings_section(
			'client_settings_client_section',
			'Configuration du client Stan Connect',
			array(),
			'stan-easy-connect',
			array( 'title' => 'Configuration du client' )
		);

		add_settings_field(
			'client_id',
			'Nom du client Stan Connect',
			array( $this, 'render_input' ),
			'stan-easy-connect',
			'client_settings_client_section',
			$this->settings_fields['client_id']
		);
		add_settings_field(
			'client_secret',
			'Code secret du client Stan Connect',
			array( $this, 'render_input' ),
			'stan-easy-connect',
			'client_settings_client_section',
			$this->settings_fields['client_secret']
		);
		add_settings_field(
			'stan_connect_in_login',
			'Activer Stan Connect',
			array( $this, 'render_input' ),
			'stan-easy-connect',
			'client_settings_client_section',
			$this->settings_fields['stan_connect_in_login']
		);
		add_settings_field(
			'test_connection',
			'',
			array( $this, 'render_button' ),
			'stan-easy-connect',
			'client_settings_client_section',
			$this->settings_fields['test_connection']
		);
		add_settings_field(
			'redirect_uri',
			'',
			array( $this, 'render_input' ),
			'stan-easy-connect',
			'client_settings_client_section',
			$this->settings_fields['redirect_uri']
		);
		add_settings_field(
			'stan_api_auth_url',
			'',
			array( $this, 'render_input' ),
			'stan-easy-connect',
			'client_settings_client_section',
			$this->settings_fields['stan_api_auth_url']
		);
	}

	/**
	 * Renders a text input
	 *
	 * @param args
	 */
	public function render_input( $args ) {
		if ( $args['type'] === 'checkbox' ) {
			$div = '<div><input type="%s" id="%s" class="large-text" name="%s" /></div>';
			if ( isset( $this->options[ $args[ 'key' ] ] ) && $this->options[ $args[ 'key' ] ] ) {
				$div = '<div><input type="%s" id="%s" class="large-text" name="%s" checked /></div>';
			}

			printf(
				$div,
				$args['type'],
				$args['key'],
				$args['name']
			);
		} else {
			printf(
				'<div><input type="%s" id="%s" class="large-text" name="%s" value="%s" /></div>',
				$args['type'],
				$args['key'],
				$args['name'],
				esc_attr( isset( $this->options[ $args[ 'key' ] ] ) ? $this->options[ $args[ 'key' ] ] : '' ),
				isset( $args[ 'disabled' ] ) ? 'disabled' : ''
			);
		}

		if ( isset( $args[ 'description' ] ) ) {
			printf(
				'<p>%s</p>',
				$args[ 'description' ]
			);
		}
	}

	public function render_button( $args ) {
		printf(
			'<div id="%s"><button id="%s_btn" class="button-secondary">%s</button></div>',
			$args['key'], $args['key'], $args['title']
		);
	}
}
