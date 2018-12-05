<?php
/**
 * Plugin Name: Mail Switcher For Developer
 * Description: Switching all mails' "to" recipient from the original to what you specify. When the plug-in becomes active, the administrator's mail address is registered. If it becomes invalid, it will be deleted from the wp_options.
 * Version: 1.0.1
 * Author: PRESSMAN
 * Author URI: https://www.pressman.ne.jp/
 * License: GNU GPL v2 or higher
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @author    PRESSMAN
 * @link      https://www.pressman.ne.jp/
 * @copyright Copyright (c) 2018, PRESSMAN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mail_Switcher_For_Developer
 */
class Mail_Switcher_For_Developer {

	/**
	 * @var Mail_Switcher_For_Developer
	 */
	private static $instance;
	private $prefix = 'msfd';
	private $option_name = 'msfd_mail_addresses';

	/**
	 * Mail_Switcher_For_Developer constructor.
	 */
	private function __construct() {

		require_once( __DIR__ . '/class/class-setting-option-page.php' );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_bar_menu', [ $this, 'customize_admin_bar_menu' ], 9999 );

		register_activation_hook( __FILE__, [ $this, 'plugin_activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'plugin_deactivate' ] );

		add_filter( 'wp_mail', [ $this, 'mail_switcher' ] );
	}

	/**
	 * Get instance
	 *
	 * @return $instance
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		};
	}

	/**
	 * Enqueue assets
	 */
	public function enqueue_assets() {
		$data = get_file_data( __FILE__, array( 'version' => 'Version' ) );
		wp_enqueue_style( $this->prefix . '-admin-bar-css', plugin_dir_url( __FILE__ ) . '/assets/css/admin-bar.css', [], $data['version'] );
	}

	/**
	 * Add admin menu ber
	 *
	 * @param array $wp_admin_ber
	 */
	public function customize_admin_bar_menu( $wp_admin_ber ) {
		$title = '<span class="ab-icon"></span><span class="ab-label">Mail Switcher is active</span>';

		$wp_admin_ber->add_menu( [
			'id'    => 'mail-switcher',
			'meta'  => [],
			'title' => $title,
		] );
	}

	/**
	 * When activated
	 */
	public function plugin_activate() {
		if ( ! get_option( $this->option_name ) ) {
			add_option( $this->option_name, get_option( 'admin_email' ) );
		}
	}

	/**
	 * When deactivated
	 */
	public function plugin_deactivate() {
		delete_option( $this->option_name );
	}

	/**
	 * Mail Switcher For Developer main function
	 *
	 * @param $atts
	 * @return mixed
	 */
	public function mail_switcher( $atts ) {
		$addresses = [];
		$to = ( is_array( $atts['to'] ) ) ? implode( "\n", $atts['to'] ) : $atts['to'];
		$atts['message'] .= <<<EOD


--- Original Mailto list -----------------------

{$to}

------------- by Mail Switcher For Developer ---

EOD;

		$address_list = explode( "\n", get_option( 'mail_addresses', '' ) );
		foreach ( $address_list as $mail ) {
			$addresses[] = trim( $mail );
		}
		$atts['to'] = $addresses;

		/**
		 * Filter
		 */
		$atts = apply_filters( $this->prefix . '_interchange', $atts );
		return $atts;
	}
}

if ( is_admin() ) {
	Mail_Switcher_For_Developer::get_instance();
};
