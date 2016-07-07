<?php
/**
 * @package Custom_Login_Page
 * @version 0.0.2
 */

/*
* Plugin Name: Divi 100 Login Page
* Plugin URI: https://elegantthemes.com/
* Description: This plugin gives you the option to customize your login screen
* Author: Elegant Themes
* Version: 0.0.2
* Author URI: http://elegantthemes.com
* Text Domain: divi-100-login-page
* License: GPL3
*/

/**
 * Register plugin to Divi 100 list
 */
class ET_Divi_100_Custom_Login_Page_Config {
	public static $instance;

	/**
	 * Hook the plugin info into Divi 100 list
	 */
	function __construct() {
		add_filter( 'et_divi_100_settings', array( $this, 'register' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'add_localization' ), 1 );

	}

	/**
	 * Gets the instance of the plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Define plugin info
	 *
	 * @return array plugin info
	 */
	public static function info() {
		$main_prefix = 'et_divi_100_';
		$plugin_slug = 'custom_login_page';

		return array(
			'main_prefix'        => $main_prefix,
			'plugin_name'        => __( 'Login Page', 'divi-100-login-page' ),
			'plugin_slug'        => $plugin_slug,
			'plugin_id'          => "{$main_prefix}{$plugin_slug}",
			'plugin_prefix'      => "{$main_prefix}{$plugin_slug}-",
			'plugin_version'     => 20160705,
			'plugin_dir_path'    => plugin_dir_path( __FILE__ ),
		);
	}

	/**
	 * et_divi_100_settings callback
	 *
	 * @param array  settings
	 * @return array settings
	 */
	function register( $settings ) {
		$info = self::info();

		$settings[ $info['plugin_slug'] ] = $info;

		return $settings;
	}
	/**
	 * Adds plugin localization
	 * Domain: divi-100-login-page
	 *
	 * @return void
	 */
	function add_localization() {
		load_plugin_textdomain( 'divi-100-login-page', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Init plugin after all plugins has been loaded
	 */
	function init() {
		// Load Divi 100 Setup.
		require_once( plugin_dir_path( __FILE__ ) . 'divi-100-setup/divi-100-setup.php' );

		// Load Login Page.
		ET_Divi_100_Custom_Login_Page::instance();
	}
}
ET_Divi_100_Custom_Login_Page_Config::instance();

/**
 * Load Login Page
 */
class ET_Divi_100_Custom_Login_Page {
	/**
	 * Unique instance of plugin
	 */
	public static $instance;
	public $config;
	protected $settings;
	protected $utils;

	/**
	 * Gets the instance of the plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->config   = ET_Divi_100_Custom_Login_Page_Config::info();
		$this->settings = maybe_unserialize( get_option( $this->config['plugin_id'] ) );
		$this->utils    = new Divi_100_Utils( $this->settings );

		// Initialize if Divi is active.
		if ( et_divi_100_is_active() ) {
			$this->init();
		}
	}

	/**
	 * Hooking methods into WordPress actions and filters
	 *
	 * @return void
	 */
	private function init() {
		add_filter( 'login_body_class',      array( $this, 'body_class' ) );
		add_action( 'login_footer',          array( $this, 'print_styles' ) );
		add_filter( 'login_headerurl',       array( $this, 'modify_login_logo_url' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

		if ( is_admin() ) {
			$settings_args = array(
				'plugin_id'   => $this->config['plugin_id'],
				'plugin_slug' => $this->config['plugin_slug'],
				'preview_dir_url' => plugin_dir_url( __FILE__ ) . 'assets/img/preview/',
				'title'       => __( 'Login Page' ),
				'fields'      => $this->setting_fields(),
				'button_save_text' => __( 'Save Changes', 'divi-100-login-page' ),
			);

			new Divi_100_Settings( $settings_args );
		}
	}

	private function wp_default_styles() {
		return array(
			'background-color'        => '#f1f1f1',
			'button-background-color' => '#0085ba',
			'button-text-color'       => '#ffffff',
		);
	}

	private function styles_defaults() {
		$defaults = $this->wp_default_styles();

		return array(
			'' => $defaults,
			'1' => array(
				'background-color'        => '#d8634f',
				'button-background-color' => '#ffffff',
				'button-text-color'       => '#d8634f',
			),
			'2' => array(
				'background-color'        => '#58575D',
				'button-background-color' => '#FF3366',
				'button-text-color'       => '#FFFFFF',
			),
			'3' => array(
				'background-color'        => '#5A5A62',
				'button-background-color' => 'EFF3366',
				'button-text-color'       => '#FFFFFF',
			),
			'4' => array(
				'background-color'        => '#333141',
				'button-background-color' => '#FF3366',
				'button-text-color'       => '#FFFFFF',
			),
			'5' => array(
				'background-color'        => '#36363E',
				'button-background-color' => '#FF3366',
				'button-text-color'       => '#FFFFFF',
			),
			'6' => array(
				'background-color'        => '#383941',
				'button-background-color' => '#FF3366',
				'button-text-color'       => '#FFFFFF',
			),
			'7' => array(
				'background-color'        => '#282939',
				'button-background-color' => '#FF3366',
				'button-text-color'       => '#FFFFFF',
			),
		);
	}

	private function get_style_default( $style = '', $option = '' ) {
		$wp_default_styles = $this->wp_default_styles();
		$valid_options     = array_keys( $wp_default_styles );
		$styles_defaults   = $this->styles_defaults();

		if ( ! in_array( $option, $valid_options ) ) {
			return false;
		}

		return isset( $styles_defaults[ $style ] ) && isset( $styles_defaults[ $style ][ $option ] ) ? $styles_defaults[ $style ][ $option ] : $wp_default_styles[ $option ];
	}

	private function setting_fields() {
		$selected_style = $this->utils->get_value( 'style', '' );

		return array(
			'style' => array(
				'type'                 => 'select',
				'preview_prefix'       => 'style-',
				'preview_height'       => 182,
				'id'                   => 'style',
				'label'                => __( 'Select Style', 'divi-100-login-page' ),
				'description'          => __( 'This style will be applied to your login screen', 'divi-100-login-page' ),
				'options'              => $this->get_styles(),
				'sanitize_callback'    => 'sanitize_text_field',
			),
			'background-color' => array(
				'type'                 => 'color',
				'id'                   => 'background-color',
				'label'                => __( 'Select Background Color', 'divi-100-login-page' ),
				'description'          => __( 'Use custom color for your login screen background', 'divi-100-login-page' ),
				'sanitize_callback'    => 'et_divi_100_sanitize_alpha_color',
				'default'              => $this->get_style_default( $selected_style, 'background-color' ),
			),
			'background-image' => array(
				'type'                 => 'upload',
				'id'                   => 'background-image',
				'label'                => __( 'Select Background Image', 'divi-100-login-page' ),
				'description'          => __( 'Use custom image for your login screen background', 'divi-100-login-page' ),
				'button_active_text'   => __( 'Change Background', 'divi-100-login-page' ),
				'button_inactive_text' => __( 'Select Background', 'divi-100-login-page' ),
				'button_remove_text'   => __( 'Remove Background', 'divi-100-login-page' ),
				'sanitize_callback'    => 'esc_url',
			),
			'logo-image' => array(
				'type'                 => 'upload',
				'id'                   => 'logo-image',
				'label'                => __( 'Select Logo Image', 'divi-100-login-page' ),
				'description'          => __( 'Use your own logo for your login screen', 'divi-100-login-page' ),
				'button_active_text'   => __( 'Change Logo', 'divi-100-login-page' ),
				'button_inactive_text' => __( 'Select Logo', 'divi-100-login-page' ),
				'button_remove_text'   => __( 'Remove Logo', 'divi-100-login-page' ),
				'sanitize_callback'    => 'esc_url',
			),
			'logo-url' => array(
				'type'                 => 'url',
				'id'                   => 'logo-url',
				'label'                => __( 'Modify Logo URL', 'divi-100-login-page' ),
				'placeholder'          => esc_url( home_url() ),
				'description'          => __( 'Use your own URL for logo on login screen', 'divi-100-login-page' ),
				'sanitize_callback'    => 'esc_url',
			),
			'button-background-color' => array(
				'type'                 => 'color',
				'id'                   => 'button-background-color',
				'label'                => __( 'Select Button Background Color', 'divi-100-login-page' ),
				'description'          => __( 'Use custom color for background button', 'divi-100-login-page' ),
				'sanitize_callback'    => 'et_divi_100_sanitize_alpha_color',
				'default'              => $this->get_style_default( $selected_style, 'button-background-color' ),
			),
			'button-text-color' => array(
				'type'                 => 'color',
				'id'                   => 'button-text-color',
				'label'                => __( 'Select Button Text Color', 'divi-100-login-page' ),
				'description'          => __( 'Use custom color text button', 'divi-100-login-page' ),
				'sanitize_callback'    => 'et_divi_100_sanitize_alpha_color',
				'default'              => $this->get_style_default( $selected_style, 'button-text-color' ),
			),
		);
	}

	/**
	 * List of valid styles
	 *
	 * @return void
	 */
	function get_styles() {
		return apply_filters( $this->config['plugin_prefix'] . 'styles', array(
			''  => __( 'Default', 'divi-100-login-page' ),
			'1' => __( 'One', 'divi-100-login-page' ),
			'2' => __( 'Two', 'divi-100-login-page' ),
			'3' => __( 'Three', 'divi-100-login-page' ),
			'4' => __( 'Four', 'divi-100-login-page' ),
			'5' => __( 'Five', 'divi-100-login-page' ),
			'6' => __( 'Six', 'divi-100-login-page' ),
			'7' => __( 'Seven', 'divi-100-login-page' ),
		) );
	}

	/**
	 * Get selected style
	 *
	 * @return string
	 */
	function get_selected_style() {
		$style = $this->utils->get_value( 'style', '' );

		return apply_filters( $this->config['plugin_prefix'] . 'get_selected_style', $style );
	}

	/**
	 * Add specific class to <body>
	 *
	 * @return array
	 */
	function body_class( $classes ) {
		// Get selected style.
		$selected_style = $this->get_selected_style();

		// Assign specific class to <body> if needed.
		if ( '' !== $selected_style ) {
			$classes[] = esc_attr( $this->config['plugin_prefix'] . '-style-' . $selected_style . ' et_divi_100_custom_login_page' );
		}

		return $classes;
	}

	/**
	 * Load front end scripts
	 *
	 * @return void
	 */
	function enqueue_frontend_scripts() {
		wp_enqueue_style( 'custom-login-pages', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), $this->config['plugin_version'] );
		wp_enqueue_style( 'custom-login-pages-icon-font', plugin_dir_url( __FILE__ ) . 'assets/css/ionicons.min.css', array(), $this->config['plugin_version'] );
		wp_enqueue_script( 'custom-login-pages-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/scripts.js', array( 'jquery' ), $this->config['plugin_version'], true );
	}

	/**
	 * Modify login logo url
	 */
	function modify_login_logo_url( $url ) {
		$custom_url = $this->utils->get_value( 'logo-url', false );

		if ( ! $custom_url || '' === $custom_url ) {
			$settings = $this->setting_fields();
			$custom_url = $settings['logo-url']['placeholder'];
		}

		return $custom_url;
	}

	/**
	 * Print background image on login page
	 *
	 * @return void
	 */
	function print_styles() {
		$setting_fields       = $this->setting_fields();
		$background_image_src = $this->utils->get_value( 'background-image', '' );
		$background_color     = $this->utils->get_value( 'background-color', '' );
		$logo_image_src       = $this->utils->get_value( 'logo-image', '' );
		$button_background_color = $this->utils->get_value( 'button-background-color', '' );
		$button_text_color    = $this->utils->get_value( 'button-text-color', '' );
		$print_css_status     = false;
		$css                  = '<style type="text/css">';

		if ( $background_color && '' !== $background_color && $setting_fields['background-color']['default'] !== $background_color ) {
			$print_css_status = true;
			$css .= sprintf(
				'html, body.et_divi_100_custom_login_page {
					background-color: %s !important;
				}',
				esc_url( $background_color )
			);
		}

		if ( $background_image_src && '' !== $background_image_src ) {
			$print_css_status = true;
			$css .= sprintf(
				'body.et_divi_100_custom_login_page {
					background: url( "%s" ) center center no-repeat !important;
					background-size: cover !important;
				}',
				esc_url( $background_image_src )
			);
		}

		if ( $logo_image_src && '' !== $logo_image_src ) {
			$print_css_status = true;
			$css .= sprintf(
				'#login h1 a {
					background: url( "%s" ) center center no-repeat !important;
					background-size: cover !important;
					background-position: center center !important;
				}',
				esc_url( $logo_image_src )
			);
		}

		if ( $button_background_color && '' !== $button_background_color && $setting_fields['button-background-color']['default'] !== $button_background_color ) {
			$print_css_status = true;
			$css .= sprintf(
				'.et_divi_100_custom_login_page .divi-login__submit input.button,
				.et_divi_100_custom_login_page .divi-login__submit input.button:hover {
					background-color: %1$s !important;
					border-color: %1$s !important;
					box-shadow: none !important;
					text-shadow: none !important;
				}',
				et_divi_100_sanitize_alpha_color( $button_background_color )
			);
		}

		if ( $button_text_color && '' !== $button_text_color && $setting_fields['button-text-color']['default'] !== $button_text_color ) {
			$print_css_status = true;
			$css .= sprintf(
				'.et_divi_100_custom_login_page .divi-login__submit input.button {
					color: %1$s !important;
				}',
				et_divi_100_sanitize_alpha_color( $button_text_color )
			);
		}

		$css .= '</style>';

		if ( $print_css_status ) {
			echo $css;
		}
	}
}
