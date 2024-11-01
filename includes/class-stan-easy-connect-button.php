<?php

class Stan_Easy_Connect_Button {

	/**
	 * Client that integrates auth process
	 */
	private $client_wrapper;

	/**
	 * @param $client_wrapper
	 */
	public function __construct( $client_wrapper ){
		$this->client_wrapper = $client_wrapper;

		add_action( 'login_form_login', array( $this, 'handle_redirect_cookie' ) );

		$this->init();
	}

	/**
	 * Initiates the button configurations.
	 *
	 * @param $settings
	 * @param $client_wrapper
	 */
	public function init() {
		add_filter( 'login_message', array( $this, 'handle_login_page' ), 99 );

		add_shortcode( Stan_Easy_Connect_Admin::$short_code, array( $this, 'make_login_button' ) );
	}

	/**
	 * Handles login related redirects
	 *
	 * @since 0.1.0
	 */
	public function handle_redirect_cookie() {
		if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'logout' ) {
			return;
		}

		$redirect_expiry = current_time('timestamp') + DAY_IN_SECONDS;

		$redirect_url = home_url( esc_url( add_query_arg( null, null ) ) );

		setcookie(
			Stan_Easy_Connect_Wrapper::$cookie_redirect_key,
			$redirect_url, $redirect_expiry,
			COOKIEPATH, COOKIE_DOMAIN, is_ssl()
		);
	}

	/**
	 * Handles filter login_message
	 *
	 * @since 0.1.0
	 * @param $message
	 *
	 * @return string
	 */
	public function handle_login_page( $message ) {
		if ( isset( $_GET[ 'login-error' ] ) ) {
			$login_error = $_GET[ 'login-error' ];
			if ( ! empty ( $login_error ) ) {
				$error = sanitize_text_field( $login_error );
				$errMsg = $_GET['message'];

				if ( isset( $errMsg ) ) {
					$message .= $this->make_error_output( $error, sanitize_text_field( $errMsg ) );
				}
			}
		}

		// login button is appended to existing messages in case of error
		// $message .= $this->make_login_button();
		return $message;
	}

	/**
	 * Displays an error message to the user
	 *
	 * @param $error_code
	 *
	 * @return string
	 */
	public function make_error_output( $error_code, $error_message ) {
		ob_start();

		?>
		<div id="login_error">
			<strong><?php echo 'Erreur de connexion'; ?>: </strong>
			<?php print esc_html($error_message); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Creates a login button
	 *
	 * @return string
	 */
	public function make_login_button() {
		if ( is_user_logged_in() ) {
			return;
		}

		wp_enqueue_style( 'stan-connect-shortcode' );

		$text = 'Se connecter avec Stan';
		$href = $this->client_wrapper->get_authentication_url();

		ob_start();

		?>
			<div id="stan-easy-connect" class="stan-button">
				<div class="stan-easy-connect--tooltip">
					<a class="stan-easy-connect--button" href="<?php print esc_url( $href ); ?>">
						<svg
						height="28px"
						viewBox="0 0 64 64"
						version="1.1"
						id="stan-connect-svg-icon"
						xmlns="http://www.w3.org/2000/svg"
						xmlns:svg="http://www.w3.org/2000/svg">
						<defs
							id="defs2">
							<linearGradient
							inkscape:collect="always"
							id="linearGradient923">
							<stop
								style="stop-color:#000000;stop-opacity:1;"
								offset="0"
								id="stop919" />
							<stop
								style="stop-color:#000000;stop-opacity:0;"
								offset="1"
								id="stop921" />
							</linearGradient>
							<linearGradient
							xlink:href="#linearGradient923"
							id="linearGradient925"
							x1="5.9670706"
							y1="42.734856"
							x2="200.4445"
							y2="42.734856"
							gradientUnits="userSpaceOnUse" />
							<linearGradient
							id="paint0_linear_3310_6195"
							x1="25.2323"
							y1="0"
							x2="25.2323"
							y2="45.754601"
							gradientUnits="userSpaceOnUse">
							<stop
								stop-color="#FF9B5F"
								id="stop2216"
								style="stop-color:#ff9b5f;stop-opacity:1" />
							<stop
								offset="1"
								stop-color="#FF0099"
								id="stop2218" />
							</linearGradient>
							<linearGradient
							xlink:href="#paint0_linear_3310_6195"
							id="linearGradient837"
							x1="47.746048"
							y1="-9.401022"
							x2="0.64766246"
							y2="45.393616"
							gradientUnits="userSpaceOnUse" />
							<linearGradient
							xlink:href="#paint0_linear_3310_6195"
							id="linearGradient1668"
							gradientUnits="userSpaceOnUse"
							x1="47.746048"
							y1="-9.401022"
							x2="0.64766246"
							y2="45.393616" />
							<linearGradient
							xlink:href="#paint0_linear_3310_6195"
							id="linearGradient1670"
							gradientUnits="userSpaceOnUse"
							x1="47.746048"
							y1="-9.401022"
							x2="0.64766246"
							y2="45.393616" />
						</defs>
						<g
							id="layer1">
							<g
							style="fill:url(#linearGradient837);fill-opacity:1"
							id="g2232"
							transform="matrix(1.0829944,0,0,1.0829944,4.6735594,7.2240177)">
							<path
								d="m 6.09676,21.4503 c 0.26701,-1.7237 0.84552,-3.4474 1.64655,-4.9943 0,0 0.94945,-2.0153 -1.37953,-3.4031 -2.22507,-1.326 -3.73813,0.0441 -3.73813,0.0441 -1.20154,2.2541 -1.958059,4.7292 -2.358571,7.2484 -0.489515,3.1822 -0.3115098,6.4528 0.578517,9.5467 0.890024,3.138 2.447574,6.055 4.895154,8.4417 9.12275,9.4582 27.41285,9.9444 37.29215,1.0607 4.5836,-4.1546 7.4317,-9.9002 7.4317,-16.353 0,-5.7015 -1.869,-10.4306 -4.8506,-14.09902 C 42.2764,4.83212 37.6482,2.09188 32.8421,0.721759 26.6119,-1.04614 21.2272,0.633365 18.2456,3.59459 c -7.9212,7.77871 -0.623,14.32001 7.4763,16.04371 3.9161,0.8397 5.8741,2.2098 6.4527,3.6241 0.1335,0.2652 0.1335,0.5746 0.089,0.884 -1.5131,7.3368 -15.6645,4.4197 -19.5361,-4.6849 -3.07063,14.0989 15.9314,19.0491 23.1407,10.5631 1.068,-1.2375 1.8245,-2.7402 2.136,-4.3313 0.356,-1.6795 0.2225,-3.4474 -0.445,-5.1269 -1.246,-3.2706 -4.6726,-6.1876 -10.7693,-7.4694 -4.0051,-0.8397 -4.8507,-0.9281 -5.4292,-2.2982 -0.089,-0.2652 -0.1335,-0.5304 -0.0445,-0.7956 0.9791,-3.66837 7.6098,-3.44738 10.1018,-2.74022 3.7826,1.06074 7.3428,3.13802 9.7903,6.18762 2.0026,2.5193 3.2931,5.7015 3.2931,9.6351 0,4.2871 -2.0025,8.2207 -5.2066,11.0935 C 30.172,42.3557 10.3244,41.3834 6.45277,27.859 5.87425,25.7817 5.78525,23.616 6.09676,21.4503 Z"
								fill="url(#paint0_linear_3310_6195)"
								id="path2212"
								style="fill:url(#linearGradient1668);fill-opacity:1" />
							<circle
								cx="8.7472401"
								cy="7.4009299"
								r="3.36431"
								fill="#ff9b5f"
								id="circle2214"
								style="fill:url(#linearGradient1670);fill-opacity:1" />
							</g>
						</g>
						</svg>
						<?php print $text; ?>
					</a>
					<span class="stan-easy-connect--tooltiptext">Vous devez être Stanner pour la connexion rapide avec Stan App</span>
				</div>
			</div>
		<?php

		return ob_get_clean();
	}
}
