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
		$this->version                = '2.0.0-alpha';
		$this->domain                 = 'workmates';
		$this->file                   = __FILE__;
		$this->basename               = plugin_basename( $this->file );
		$this->plugin_dir             = plugin_dir_path( $this->file );
		$this->plugin_url             = plugin_dir_url( $this->file );
		$this->lang_dir               = trailingslashit( $this->plugin_dir . 'languages' );
		$this->includes_dir           = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url           = trailingslashit( $this->plugin_url . 'includes' );
		$this->assets_url             = trailingslashit( $this->plugin_url . 'assets' );
		$this->plugin_js              = trailingslashit( $this->includes_url . 'js' );
		$this->plugin_css             = trailingslashit( $this->includes_url . 'css' );

		/** Component specific globals ********************************************/
		// Maybe one day... but not used right now
		$this->component_id   = self::$init_vars['component_id'];
		$this->component_slug = self::$init_vars['component_root_slug'];
		$this->component_name = self::$init_vars['component_name'];

		//Group specific
		$this->group_component_name = 'Invite Workmates';
		$this->group_component_slug = 'workmate-invites';
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
	 * Checks if current blog is the one where is activated BuddyPress
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	public static function buddypress_site_check() {
		if ( ! function_exists( 'bp_get_root_blog_id' ) ) {
			return false;
		}

		if ( (int) get_current_blog_id() !== (int) bp_get_root_blog_id() ) {
			return false;
		}

		return true;
	}

	/**
	 * Includes the needed files
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	private function includes() {
		require $this->includes_dir . 'functions.php';
		require $this->includes_dir . 'filters.php';

		if ( WP_DEBUG ) {
			require $this->includes_dir . 'deprecated.php';
		}
	}

	/**
	 * Display a notice if the required BuddyPress config is not available.
	 *
	 * @since 2.0.0
	 */
	public function deactivate_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php esc_html_e( 'La configuration actuelle de BuddyPress ne permet pas à Workmates de fonctionner. Désactivez cette extension ou revoyez votre configuration BuddyPress.', 'workmates' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Include BP Nouveau files.
	 *
	 * @since 2.0.0
	 */
	public function get_nouveau_files() {
		$bp = buddypress();

		if ( ! isset( $bp->theme_compat->packages['nouveau'] ) || ! bp_is_active( 'groups' ) ) {
			add_action( 'all_admin_notices', array( $this, 'deactivate_notice' ) );
			return;
		}

		$this->tp_url = trailingslashit( $bp->theme_compat->packages['nouveau']->__get( 'url' ) );
		$this->tp_dir = trailingslashit( $bp->theme_compat->packages['nouveau']->__get( 'dir' ) );

		// Include needed BP Nouveau files
		require $this->tp_dir . 'includes/groups/classes.php';
		require $this->tp_dir . 'includes/groups/functions.php';

		if ( wp_doing_ajax() ) {
			require $this->tp_dir . 'includes/groups/ajax.php';
		}
	}

	/**
	 * Remove some Nouveau AJAX actions to prevent conflicts with Legacy.
	 *
	 * @since 2.0.0
	 */
	public function remove_extra_ajax_actions() {
		$ajax_actions = array(
			array( 'groups_filter'             => array( 'function' => 'bp_nouveau_ajax_object_template_loader', ) ),
			array( 'groups_join_group'         => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        ) ),
			array( 'groups_leave_group'        => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        ) ),
			array( 'groups_request_membership' => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        ) ),
		);

		foreach ( $ajax_actions as $ajax_action ) {
			$action = key( $ajax_action );

			remove_action( 'wp_ajax_' . $action, $ajax_action[ $action ]['function'] );
		}
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	private function setup_hooks() {
		// Bail if BuddyPress version is not supported or current blog is not the one where BuddyPress is activated
		if ( ! $this->bp_config_check() || ! self::buddypress_site_check() ) {
			add_action( 'all_admin_notices', array( $this, 'deactivate_notice' ) );
			return;
		}

		add_action( 'bp_init',                   array( $this, 'load_textdomain'   ), 6 );
		add_action( 'bp_setup_theme',            array( $this, 'get_nouveau_files' )    );
		add_action( 'bp_enqueue_scripts',        array( $this, 'cssjs'             )    );
		add_action( 'bp_messages_setup_globals', array( $this, 'autocomplete_all'  )    );

		if ( bp_is_active( 'groups' ) ) {
			add_action( 'groups_setup_nav', 'bp_nouveau_group_setup_nav' );
			add_filter( 'groups_create_group_steps', 'bp_nouveau_group_invites_create_steps', 10, 1 );

			add_action( 'admin_init', array( $this, 'remove_extra_ajax_actions' ), 20 );

			if ( bp_is_active( 'friends' ) ) {
				add_action( 'bp_settings_setup_nav', 'bp_nouveau_groups_invites_restriction_nav' );
				add_filter( 'bp_settings_admin_nav', 'bp_nouveau_groups_invites_restriction_admin_nav', 10, 1 );
			}
		}

		if ( bp_is_active( 'messages' ) ) {
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
		if ( ! bp_is_active( 'friends' ) && bp_is_active( 'messages' ) ) {
			buddypress()->messages->autocomplete_all = true;
		}
	}

	/**
	 * Enqueues the js and css files only if WorkMates needs it
	 *
	 * The goal here is to get the built in WordPress media editor scripts
	 * without plupload.
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @since 2.0.0 Use the BP Nouveau Group Invites UI.
	 */
	public function cssjs() {
		if ( ! bp_is_active( 'groups' ) ) {
			return;
		}

		if ( ! bp_is_group_invites() && ! ( bp_is_group_create() && bp_is_group_creation_step( 'group-invites' ) ) ) {
			return;
		}

		// Get script.
		$scripts = bp_nouveau_groups_register_scripts( array(
			'bp-nouveau' => array(),
		) );

		// Bail if no scripts
		if ( ! isset( $scripts['bp-nouveau-group-invites'] ) ) {
			return;
		}

		$min = '';

		wp_enqueue_style(
			'workmates-group-invites',
			sprintf( '%1$sstyle%2$s.css', $this->assets_url . 'css/', $min ),
			array( 'dashicons' ),
			$this->version
		);

		$script = $scripts['bp-nouveau-group-invites'];
		array_shift( $script['dependencies'] );

		wp_register_script(
			'bp-nouveau-group-invites',
			$this->tp_url . '/' . sprintf( $script['file'], $min ),
			$script['dependencies'],
			$this->version,
			$script['footer']
		);

		wp_enqueue_script(
			'workmates-group-invites',
			$this->assets_url . sprintf( '/js/script%s.js', $min ),
			array( 'bp-nouveau-group-invites' ),
			$this->version,
			true
		);

		$localized_data = bp_nouveau_groups_localize_scripts( array(
			'nonces' => array(
				'groups' => wp_create_nonce( 'bp_nouveau_groups' ),
			) )
		);

		// Use member types to add new filters to the Group Invites UI.
		$member_types = bp_get_member_types( array(), 'objects' );
		$order        = 5;

		if ( ! empty( $member_types ) && is_array( $member_types ) ) {
			foreach ( $member_types as $type_key => $type ) {
				$order += 1;

				$localized_data['group_invites']['nav'][] = array(
					'id'      => '_wm_mt_' . $type_key,
					'caption' => esc_html( $type->labels['name'] ),
					'order'   => $order,
				);
			}

			// Reorder the nav !
			$localized_data['group_invites']['nav'] = bp_sort_by_key(
				$localized_data['group_invites']['nav'],
				'order',
				'num'
			);
		}

		wp_localize_script( 'bp-nouveau-group-invites', 'BP_Nouveau', $localized_data );
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
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/workmates/' . $mofile;

		// Look in global /wp-content/languages/workmates folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/workmates/languages/ folder
		load_textdomain( $this->domain, $mofile_local );
	}


}

endif;

// Let's start !
function workmates() {
	return WorkMates::get_instance();
}
// Not too early and not too late ! 9 seems ok ;)
add_action( 'bp_include', 'workmates', 9 );
