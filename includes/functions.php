<?php
/**
 * WorkMates functions.
 *
 * @package WorkMates
 * @since  1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Returns plugin version
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_plugin_version() {
	return workmates()->version;
}

/**
 * Returns plugin's dir
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_plugin_dir() {
	return workmates()->plugin_dir;
}

/**
 * Returns plugin's includes dir
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_includes_dir() {
	return workmates()->includes_dir;
}

/**
 * Returns plugin's assets url
 *
 * @package WorkMates
 * @since 2.0.0
 */
function workmates_get_assets_url() {
	return workmates()->assets_url;
}

/**
 * Returns plugin's js url
 *
 * @package WorkMates
 * @since  1.0
 * @since  2.0.0 JS assets were moved in the /assets directory
 */
function workmates_get_js_url() {
	return trailingslashit( workmates_get_assets_url() . 'js' );
}

/**
 * Returns plugin's js url
 *
 * @package WorkMates
 * @since  1.0
 * @since  2.0.0 CSS assets were moved in the /assets directory
 */
function workmates_get_css_url() {
	return trailingslashit( workmates_get_assets_url() . 'css' );
}

/**
 * Get the JS minified suffix.
 *
 * @since  1.0.0
 *
 * @return string the JS minified suffix.
 */
function workmates_min_suffix() {
	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG )  {
		$min = '';
	}

	/**
	 * Filter here to edit the minified suffix.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $min The minified suffix.
	 */
	return apply_filters( 'workmates_min_suffix', $min );
}

/**
 * Display a notice if the required BuddyPress config is not available.
 *
 * @since 2.0.0
 */
function workmates_deactivate_notice() {
	?>
	<div class="notice notice-error is-dismissible">
		<p>
			<?php esc_html_e( 'La configuration actuelle de BuddyPress ne permet pas à WorkMates de fonctionner. Désactivez cette extension ou revoyez votre configuration BuddyPress.', 'workmates' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Wraper to toggle return 0 in filters.
 *
 * @since  2.0.0
 *
 * @return integer 0
 */
function workmates_return_zero() {
	return __return_zero();
}

/**
 * Temporarly replace the Legacy Stack by the Nouveau one.
 *
 * @since 2.0.0
 *
 * @param  array $stack A list of template locations.
 * @return array        A list of template locations.
 */
function workmates_alter_template_stack( $stack = array() ) {
	remove_filter( 'bp_get_template_stack', 'workmates_alter_template_stack', 10, 1 );

	$legacy_path  = untrailingslashit( bp_get_theme_compat_dir() );
	$nouveau_path = untrailingslashit( workmates()->tp_dir );

	foreach ( $stack as $key => $path ) {
		if ( 0 !== strpos( $path, $legacy_path ) ) {
			continue;
		}

		$stack[ $key ] = str_replace( $legacy_path, $nouveau_path, $path );
	}

	return $stack;
}

/**
 * Loads the translation files
 *
 * @since 2.0.0
 */
function workmates_load_textdomain() {
	$wm = workmates();

	load_plugin_textdomain( $wm->domain, false, trailingslashit( basename( $wm->plugin_dir ) ) . 'languages' );
}
add_action( 'bp_init', 'workmates_load_textdomain', 6 );
