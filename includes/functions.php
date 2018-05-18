<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* global functions */

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
 * Returns plugin's includes url
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_includes_url() {
	return workmates()->includes_url;
}

/**
 * Returns plugin's js url
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_js_url() {
	return workmates()->plugin_js;
}

/**
 * Returns plugin's js url
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_css_url() {
	return workmates()->plugin_css;
}

/**
 * Returns plugin's component id
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_component_id() {
	return workmates()->component_id;
}

/**
 * Returns plugin's component slug
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses buddypress() BuddyPress main instance
 * @uses workmates() plugin's main instance
 */
function workmates_get_component_slug() {
	$slug = !empty( buddypress()->{workmates_get_component_id()}->slug ) ? buddypress()->{workmates_get_component_id()}->slug : workmates()->component_slug;
	return $slug;
}

/**
 * Returns plugin's component name
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_component_name() {
	return workmates()->component_name;
}

/**
 * Returns plugin's Group component name
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_group_component_name() {
	return apply_filters( 'workmates_get_group_component_name', workmates()->group_component_name );
}

/**
 * Returns plugin's Group component slug
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_group_component_slug() {
	return workmates()->group_component_slug;
}

/**
 * Are we on the group invite workmates screen ?
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses bp_is_single_item() to check we're on a single group
 * @uses bp_is_groups_component() to check we're in the group component
 * @uses bp_is_current_action() to check for current action
 * @uses workmates_get_group_component_slug() to get plugin's group slug
 */
function workmates_is_group_front() {
	if( bp_is_single_item() && bp_is_groups_component() && bp_is_current_action( workmates_get_group_component_slug() ) )
		return true;

	return false;
}

/**
 * Are we on the group invite workmates create screen ?
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses bp_is_group_creation_step() to check for the workmates groups extension create step
 * @uses workmates_get_group_component_slug() to get the slug of the component
 */
function workmates_is_group_create() {
	if( bp_is_group_creation_step( workmates_get_group_component_slug() ) )
		return true;

	return false;
}

/**
 * Prepare users before sending a json object to the WorkMates editor
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses bp_core_fetch_avatar() to get user's avatar
 */
function workmates_prepare_user_for_js( $users ) {

	$response = array(
		'id'           => intval( $users->ID ),
		'name'         => $users->display_name,
		'avatar'       => htmlspecialchars_decode( bp_core_fetch_avatar( array(
			'item_id' => $users->ID,
			'object' => 'user',
			'type' => 'full',
			'width' => 150,
			'height' => 150,
			'html' => false )
		) ),
	);

	return apply_filters( 'workmates_prepare_user_for_j', $response, $users );
}

/**
 * This is a copy of the BuddyPress function to send invites
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses bp_is_single_item() to make sure we're on a single item
 * @uses buddypress() to get BuddyPress main global
 * @uses bp_is_action_variable() to check the first action variable is send
 * @uses check_admin_referer() for security reasons
 * @uses groups_invite_user() to create an invite for the user
 * @uses groups_send_invites() to send the invites (screen notification & mail)
 * @uses bp_loggedin_user_id() to get current user's id
 * @uses bp_core_add_message() to add a template notice
 * @uses bp_core_redirect() to safely redirect the user
 * @uses bp_get_group_permalink() to build the link to the group
 * @uses bp_action_variable() to get the first variable
 * @uses bp_core_load_template() to load the right template
 * @uses bp_do_404() to eventually display a 404
 */
function workmates_group_invite() {

	if ( ! bp_is_single_item() ) {
		return false;
	}

	$bp = buddypress();

	if ( bp_is_action_variable( 'send', 0 ) ) {

		if ( ! check_admin_referer( 'workmates_send_invites', '_wpnonce_send_invites' ) ) {
			return false;
		}

		if ( ! empty( $_POST['workmates'] ) ) {
			foreach( (array) $_POST['workmates'] as $workmate ) {
				groups_invite_user( array( 'user_id' => $workmate, 'group_id' => $bp->groups->current_group->id ) );
			}
		}

		// Send the invites.
		groups_send_invites( bp_loggedin_user_id(), $bp->groups->current_group->id );
		bp_core_add_message( __('Group invites sent.', 'workmates') );
		do_action( 'workmates_group_invite', $bp->groups->current_group->id );
		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) );

	} elseif ( ! bp_action_variable( 0 ) ) {
		// Show send invite page
		bp_core_load_template( apply_filters( 'workmates_template_group_invite', 'groups/single/home' ) );

	} else {
		bp_do_404();
	}
}

if ( ! function_exists( 'bp_nouveau_single_item_subnav_classes' ) ) :
/**
 * Makes sure the function exists as it is used in the invites JS Template.
 *
 * @since 2.0.0
 */
function bp_nouveau_single_item_subnav_classes() {
	echo 'group-subnav bp-invites-nav';
}
endif;

if ( ! function_exists( 'bp_nouveau_search_default_text' ) ) :
/**
 * Makes sure the function exists as it is used in the invites JS Template.
 *
 * @since 2.0.0
 */
function bp_nouveau_search_default_text() {
	esc_html_e( 'Rechercher un membre', 'workmates' );
}
endif;

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
 * Override the Send Invites template.
 *
 * @since 2.0.0
 *
 * @param  array  $templates The list of possible templates.
 * @param  string $slug      The requested template.
 * @return array             A new list of one specific template.
 */
function workmates_get_group_invites_template( $templates = array(), $slug = '' ) {
	if ( 'groups/single/send-invites' !== $slug ) {
		return $templates;
	}

	add_filter( 'bp_get_template_stack', 'workmates_alter_template_stack', 10, 1 );

	return array( 'common/js-templates/invites/index.php' );
}
add_filter( 'bp_get_template_part', 'workmates_get_group_invites_template', 10, 2 );
