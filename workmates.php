<?php
/**
 * @buddypress-plugin
 * Plugin Name:       WorkMates
 * Plugin URI:        http://imathi.eu/tag/workmates/
 * Description:       Intègre l'interface des invitations de groupe du pack de gabarits de Nouveau dans celui de l'héritage de BP Default.
 * Version:           2.0.0-alpha
 * Author:            imath
 * Author URI:        http://imathi.eu
 * Text Domain:       workmates
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/imath/workmates
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WorkMates' ) ) :
/**
 * Main WorkMates Class
 *
 * @since WorkMates (1.0)
 */
class WorkMates {
	/**
	 * Instance of this class.
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin
	 *
	 * @package WorkMates
	 * @since  1.0
	 * @since  2.0.0 Do not call deprecated setup_hooks() method anymore.
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package WorkMates
	 * @since 1.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Check BuddyPress required config is in place.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean True if required BuddyPress config is there. False otherwise.
	 */
	public function bp_config_check() {
		return function_exists( 'bp_check_theme_template_pack_dependency' ) && 'legacy' === bp_get_theme_package_id();
	}

	/**
	 * Sets some globals for the plugin
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	private function setup_globals() {
		/** WorkMates globals ********************************************/
		$this->version      = '2.0.0-alpha';
		$this->domain       = 'workmates';
		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url( $this->file );
		$this->lang_dir     = trailingslashit( $this->plugin_dir . 'languages' );
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );
		$this->assets_url   = trailingslashit( $this->plugin_url . 'assets' );
		$this->is_legacy    = $this->bp_config_check();
	}

	/**
	 * Includes the needed files
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	private function includes() {
		require $this->includes_dir . 'functions.php';

		// You don't need this plugin! Nouveau is great :)
		if ( ! $this->is_legacy ) {
			add_action( 'all_admin_notices', 'workmates_deactivate_notice' );

		// Let's play
		} else {

			// Get some Nouveau dependencies
			require $this->includes_dir . 'dependency.php';

			if ( bp_is_active( 'groups' ) ) {
				require $this->includes_dir . 'group-invites/functions.php';
			}

			if ( bp_is_active( 'messages' ) ) {
				require $this->includes_dir . 'messages/functions.php';
			}

			// Include the hooks
			require $this->includes_dir . 'hooks.php';
		}

		if ( WP_DEBUG ) {
			require $this->includes_dir . 'deprecated.php';
		}
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	private function setup_hooks() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * In case friends component is inactive and message is active
	 * defines autocomplete_all constant to true so that it's easier
	 * to send a private message using the compose screen
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function autocomplete_all( ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Loads the translation files
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function load_textdomain() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Checks BuddyPress version
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public static function buddypress_version_check() {
		_deprecated_function( __METHOD__, '2.0.0' );
		// taking no risk
		if ( ! defined( 'BP_VERSION' ) ) {
			return false;
		}

		return version_compare( BP_VERSION, '3.0', '>=' );
	}

	/**
	 * Checks if current blog is the one where is activated BuddyPress
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public static function buddypress_site_check() {
		_deprecated_function( __METHOD__, '2.0.0', 'bp_is_root_blog()' );

		return bp_is_root_blog();
	}
}

endif;

// Let's start !
function workmates() {
	return WorkMates::get_instance();
}
// Not too early and not too late ! 9 seems ok ;)
add_action( 'bp_include', 'workmates', 9 );
