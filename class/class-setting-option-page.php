<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mail_Switcher_For_Developer_Option_Settings
 */
class Mail_Switcher_For_Developer_Option_Settings {

	/**
	 * @var Mail_Switcher_For_Developer_Option_Settings
	 */
	private static $instance;
	public $menu_title    = 'Mail Switcher For Developer';
	public $menu_slug     = 'mail-switcher-for-developer';
	public $filed_id      = 'msfd_mail_addresses';

	/**
	 * Mail_Switcher_For_Developer_Option_Settings constructor
	 */
	private function __construct() {
		add_action( 'admin_menu', [ $this, 'create_settings' ] );
		add_action( 'admin_init', [ $this, 'setup_sections' ] );
		add_action( 'admin_init', [ $this, 'setup_fields' ] );
	}

	/**
	 * Get instance
	 *
	 * @return $instancez
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		};
	}

	/**
	 * Create settings
	 */
	public function create_settings() {
		add_options_page( $this->menu_title, $this->menu_title, 'manage_options', $this->menu_slug, [ $this, 'settings_content' ] );
	}

	/**
	 * Display settings
	 */
	public function settings_content() {
		include( dirname( __FILE__ ) . '/../view/setting.php' );
	}

	/**
	 * Options setup
	 */
	public function setup_sections() {
		$discription = 'Switching all mails\'(*) "to" recipient from the original to what you specify below.<p>*This plugin only affects mails being sent by "wp_mail()".</p>';
		add_settings_section( $this->menu_slug . '_section', $discription, [], $this->menu_slug );
	}

	/**
	 * Fields setup
	 */
	public function setup_fields() {
		$label   = 'Developer\'s mail addresses';
		$type    = 'textarea';
		$section = $this->menu_slug . '_section';
		$desc    = 'To register multiple mail addresses, separate them with line breaks. <br />If it is not registered, it will be sent to the administrator\'s email address.';

		add_settings_field( $this->filed_id, $label, [ $this, 'field_callback' ], $this->menu_slug, $section, [ 'type' => $type, 'desc' => $desc ] );
		$field_validator = ( method_exists( $this, 'validator_' . $this->filed_id ) ) ? 'validator_' . $this->filed_id : 'validator_common';
		register_setting( $this->menu_slug, $this->filed_id, [ $this, $field_validator ] );
	}

	/**
	 * Field callback
	 *
	 * @param array $field
	 */
	public function field_callback( $field ) {
		?>
			<textarea name="<?php echo $this->filed_id; ?>" id="<?php echo $this->filed_id; ?>" type="<?php echo $field['type']; ?>" rows="5" cols="30"><?php
				echo $mail = $this->get_saved_mail_address();
				if ( get_option( 'admin_email' ) === $mail ) {
					update_option( $this->filed_id, $mail );
				}
			?></textarea>
			<p class="description">
				<?php echo $field['desc']; ?>
			</p>
		<?php
	}

	/**
	 * Common fields validator
	 *
	 * @param mixed $input
	 * @return mixed $input
	 */
	public function validator_common( $input ) {
		return $input;
	}

	/**
	 * Mail_addresses fields validator
	 *
	 * @param string $input
	 * @return string $input
	 */
	public function validator_msfd_mail_addresses( $input ) {

		if ( '' === $input ) {
			add_settings_error(
				$this->menu_slug,
				'submit',
				'Please enter your mail address',
				'error'
			);

			return $this->get_saved_mail_address();
		}

		// Validate email addresses
		$input = str_replace( [ "\r\n", "\r", "\n" ], "\n", $input );
		$list  = explode( "\n", $input );
		foreach ( $list as $v ) {
			if ( ! filter_var( $v, FILTER_VALIDATE_EMAIL ) ) {
				add_settings_error(
					$this->menu_slug,
					'submit',
					'Invalid mail address is included.',
					'error'
				);

				return $this->get_saved_mail_address();
			}
		}

		return $input;
	}

	/**
	 * Get mail address saved in wp_options
	 *
	 * @return string
	 */
	public function get_saved_mail_address() {
		if ( get_option( $this->filed_id ) ) {
			return get_option( $this->filed_id );
		} else {
			return get_option( 'admin_email' );
		}
	}
}

if ( is_admin() ) {
	Mail_Switcher_For_Developer_Option_Settings::get_instance();
};
