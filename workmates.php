<?php
/**
 * @package   WorkMates
 * @author    imath https://twitter.com/imath
 * @license   GPL-2.0+
 * @link      http://imathi.eu
 * @copyright 2013 imath
 *
 * @wordpress-plugin
 * Plugin Name:       WorkMates
 * Plugin URI:        http://imathi.eu/tag/workmates/
 * Description:       Customizes BuddyPress on some behaviors (that i've needed) for its use in a company
 * Version:           1.0-beta3
 * Author:            imath
 * Author URI:        http://imathi.eu
 * Text Domain:       rendez-vous
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/imath/workmates
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


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
	 * @since    1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Some init vars
	 *
	 * @package WorkMates
	 * @since    1.0
	 *
	 * @var      array
	 */
	public static $init_vars = array(
		'component_id'        => 'workmates',
		'component_root_slug' => 'workmates',
		'component_name'      => 'WorkMates',
		'bp_version_required' => '2.2'
	);

	/**
	 * Initialize the plugin
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_hooks();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package WorkMates
	 * @since 1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets some globals for the plugin
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	private function setup_globals() {
		/** WorkMates globals ********************************************/
		$this->version                = '1.0-beta3';
		$this->domain                 = 'workmates';
		$this->file                   = __FILE__;
		$this->basename               = plugin_basename( $this->file );
		$this->plugin_dir             = plugin_dir_path( $this->file );
		$this->plugin_url             = plugin_dir_url( $this->file );
		$this->lang_dir               = trailingslashit( $this->plugin_dir . 'languages' );
		$this->includes_dir           = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url           = trailingslashit( $this->plugin_url . 'includes' );
		$this->plugin_js              = trailingslashit( $this->includes_url . 'js' );
		$this->plugin_css             = trailingslashit( $this->includes_url . 'css' );

		/** Component specific globals ********************************************/
		// Maybe one day... but not used right now
		$this->component_id         = self::$init_vars['component_id'];
		$this->component_slug       = self::$init_vars['component_root_slug'];
		$this->component_name       = self::$init_vars['component_name'];

		//Group specific
		$this->group_component_name = 'Invite Workmates';
		$this->group_component_slug = 'workmate-invites';

	}

	/**
	 * Checks BuddyPress version
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	public static function buddypress_version_check() {
		// taking no risk
		if( !defined( 'BP_VERSION' ) )
			return false;

		return version_compare( BP_VERSION, self::$init_vars['bp_version_required'], '>=' );
	}

	/**
	 * Checks if current blog is the one where is activated BuddyPress
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	public static function buddypress_site_check() {
		global $blog_id;

		if( !function_exists( 'bp_get_root_blog_id' ) )
			return false;

		if( $blog_id != bp_get_root_blog_id() )
			return false;

		return true;
	}

	/**
	 * Includes the needed files
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	private function includes() {
		require( $this->includes_dir . 'functions.php' );
		require( $this->includes_dir . 'filters.php' );
		require( $this->includes_dir . 'ajax.php' );
		require( $this->includes_dir . 'screens.php' );
		require( $this->includes_dir . 'editor.php' );
		require( $this->includes_dir . 'classes.php' );
		require( $this->includes_dir . 'template.php' );

		if( bp_is_active( 'groups' ) && ! bp_is_active( 'friends' ) && self::buddypress_version_check() )
			require( $this->includes_dir . 'groups.php' );

	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	private function setup_hooks() {
		// Bail if BuddyPress version is not supported or current blog is not the one where BuddyPress is activated
		if( ! self::buddypress_version_check() || ! self::buddypress_site_check() )
			return;

		//Actions
		add_action( 'bp_init',                   array( $this, 'load_textdomain'  ), 6 );
		add_action( 'bp_enqueue_scripts',        array( $this, 'cssjs'            )    );
		add_action( 'bp_messages_setup_globals', array( $this, 'autocomplete_all' )    );

		//Filters
		if( bp_is_active( 'groups' ) ) {
			add_filter( 'groups_forbidden_names', array( $this, 'groups_forbidden_names' ), 10, 1 );
		}

		if( bp_is_active( 'messages' ) ) {
			add_filter( 'bp_core_search_users_count_sql', 'workmates_filter_message_ac_count', 10, 1 );
			add_filter( 'bp_core_search_users_sql', 'workmates_filter_message_ac_select', 10, 3 );
		}

	}

	/**
	 * In case friends component is inactive and message is active
	 * defines autocomplete_all constant to true so that it's easier
	 * to send a private message using the compose screen
	 *
	 * @package WorkMates
	 * @since 1.0
	 *
	 * @uses buddypress() to get BuddyPress main instance
	 * @uses bp_is_active() to check a component is active
	 */
	public function autocomplete_all( ) {
		$bp = buddypress();

		if( ! bp_is_active( 'friends' ) && bp_is_active( 'messages' ) )
			$bp->messages->autocomplete_all = true;
	}

	/**
	 * Enqueues the js and css files only if WorkMates needs it
	 *
	 * The goal here is to get the built in WordPress media editor scripts
	 * without plupload.
	 *
	 * @package WorkMates
	 * @since 1.0
	 *
	 * @uses bp_is_active() to check if the BuddyPress Group component is active
	 * @uses workmates_is_group_front() to check if we're in the invite workmates screen
	 * @uses workmates_is_group_create() to check we're in the invite workmates create screen
	 * @uses wp_register_script() to register a new script
	 * @uses wp_localize_script() to attach some vars to it
	 * @uses wp_enqueue_script() to safely add our script to WordPress queue
	 * @uses wp_enqueue_style() to safely add our style to WordPress queue
	 * @uses workmates_enqueue_editor() to load a specific version of WP Media Editor
	 */
	public function cssjs() {

		if( ! bp_is_active( 'groups' ) )
			return;

		if( workmates_is_group_front() || workmates_is_group_create() ) {
			$suffix = SCRIPT_DEBUG ? '' : '.min';

			wp_register_script( 'workmates-plupload', includes_url( "js/plupload/wp-plupload$suffix.js" ), array(), $this->version, 1 );
			wp_localize_script( 'workmates-plupload', 'pluploadL10n', array() );
			wp_register_script( 'workmates-media-views', includes_url( "js/media-views$suffix.js" ), array( 'utils', 'media-models', 'workmates-plupload', 'jquery-ui-sortable' ), $this->version, 1 );
			wp_register_script( 'workmates-media-editor', includes_url( "js/media-editor$suffix.js" ), array( 'shortcode', 'workmates-media-views' ), $this->version, 1 );
			wp_register_script( 'workmates-modal', $this->plugin_js . "workmates-backbone$suffix.js", array( 'workmates-media-editor', 'jquery-ui-datepicker' ), $this->version, 1 );

			wp_enqueue_style( 'workmates-modal-css', $this->plugin_css . "workmates-editor$suffix.css", array(), $this->version );

			// Enqueues a specific WP Media Editor (with no support for file upload)
			workmates_enqueue_editor();
		}

	}

	/**
	 * Adds our component name to group forbidden names
	 *
	 * Let's avoid troubles between WorkMates component user nav and group nav
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	public function groups_forbidden_names( $forbidden = array() ) {
		$forbidden = array_merge( $forbidden, array( $this->component_slug, $this->component_name ) );

		return $forbidden;
	}

	/**
	 * Loads the translation files
	 *
	 * @package WorkMates
	 * @since 1.0
	 *
	 * @uses get_locale() to get the language of WordPress config
	 * @uses load_texdomain() to load the translation if any is available for the language
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/rendez-vous/' . $mofile;

		// Look in global /wp-content/languages/rendez-vous folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/rendez-vous/languages/ folder
		load_textdomain( $this->domain, $mofile_local );
	}


}

// Let's start !
function workmates() {
	return WorkMates::get_instance();
}
// Not too early and not too late ! 9 seems ok ;)
add_action( 'bp_include', 'workmates', 9 );

endif;
