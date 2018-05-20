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

/**
 * Add the Group Invite messages to the Groups Loop into the User's invites sceen.
 *
 * @since  2.0.0
 *
 * @param  boolean            $has_groups True if Groups were found. False otherwise.
 * @param  BP_Groups_Template $g_template The Groups loop Object
 * @param  array              $r          The Loop's queried arguments.
 * @return boolean                        True if Groups were found. False otherwise.
 */
function workmates_attach_group_invites_message( $has_groups = true, BP_Groups_Template $g_template, $r = array() ) {
	if ( ! isset( $r['type'] ) || 'invites' !== $r['type'] || empty( $g_template->groups ) ) {
		return $has_groups;
	}

	global $wpdb, $groups_template;
	$bp = buddypress();

	$gids = wp_list_pluck( $g_template->groups, 'id' );
	$group_list = implode( ',', wp_parse_id_list( $gids ) );
	$where = array(
		"group_id IN ({$group_list})",
		$wpdb->prepare( 'user_id = %d AND is_confirmed = 0', bp_loggedin_user_id() ),
	);

	$messages = $wpdb->get_results( "SELECT group_id, comments FROM {$bp->groups->table_name_members} WHERE " . implode( ' AND ', $where ), OBJECT_K );

	foreach ( $groups_template->groups as $k => $group ) {
		$g = (int) $group->id;

		if ( ! isset( $messages[$g] ) ) {
			continue;
		}

		$groups_template->groups[$k]->invite_message = $messages[$g]->comments;
	}

	return $has_groups;
}
add_filter( 'bp_has_groups', 'workmates_attach_group_invites_message', 10, 3 );

/**
 * Outputs the Invite message into the User's group invites loop.
 *
 * @since  2.0.0
 */
function workmates_output_group_invites_message() {
	global $groups_template;

	if ( empty( $groups_template->group->invite_message ) ) {
		return;
	}

	printf( '<h5>%1$s</h5><p>%2$s</p>',
		__( 'Message d\'invitation', 'workmates' ),
		wp_kses( $groups_template->group->invite_message, array() )
	);
}
add_action( 'bp_group_invites_item', 'workmates_output_group_invites_message' );

/**
 * Saves the invite message into the Group Members table.
 *
 * @since  2.0.0
 *
 * @param  BP_Groups_Member $group_member The group member object.
 */
function workmates_group_invites_save_message( BP_Groups_Member $group_member ) {
	$bp = buddypress();

	if ( isset( $bp->groups->invites_message ) ) {
		$group_member->comments = $bp->groups->invites_message;
	}
}
add_filter( 'groups_member_before_save', 'workmates_group_invites_save_message', 10, 1 );

/**
 * Makes sure the Group Invites message is appended to the email sent.
 *
 * @since  2.0.0
 *
 * @param  WP_Post  $post  The post object containing html & plain email text.
 * @param  BP_Email $email The email (object) about to be sent.
 * @return WP_Post         The post object containing html & plain email text.
 */
function workmates_group_invites_set_message( WP_Post $post, BP_Email $email ) {
	$bp = buddypress();

	if ( empty( $bp->groups->invites_message ) || ! $post->ID ) {
		return $post;
	}

	$email_types = wp_get_object_terms( $post->ID, bp_get_email_tax_type(), array( 'fields' => 'names' ) );
	$email_type  = reset( $email_types );

	if ( 'groups-invitation' !== $email_type ) {
		return $post;
	}

	$message = sprintf( __( 'Message d’invitation : %s', 'workmates' ), wp_strip_all_tags( $bp->groups->invites_message ) );
	$post->post_content .= "\n" . $message;
	$post->post_excerpt .= "\n\n" . $message;

	return $post;
}
add_filter( 'bp_email_set_post_object', 'workmates_group_invites_set_message', 10, 2 );

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
 * Use the Group Invites JS Templates in the Group "Invite" create step.
 *
 * @since 2.0.0
 */
function workmates_get_group_create_invites_template() {
	add_filter( 'bp_get_template_stack', 'workmates_alter_template_stack', 10, 1 );
	remove_filter( 'bp_get_total_friend_count', 'workmates_return_zero' );

	bp_get_template_part( 'common/js-templates/invites/index' );
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
	if ( 'groups/single/send-invites' !== $slug && ! ( 'groups/create' === $slug && bp_is_group_creation_step( 'group-invites' ) ) ) {
		return $templates;
	}

	if ( bp_is_group_creation_step( 'group-invites' ) ) {
		add_filter( 'bp_get_total_friend_count', 'workmates_return_zero' );
		add_action( 'groups_custom_create_steps', 'workmates_get_group_create_invites_template' );
		return $templates;
	}

	add_filter( 'bp_get_template_stack', 'workmates_alter_template_stack', 10, 1 );

	return array( 'common/js-templates/invites/index.php' );
}
add_filter( 'bp_get_template_part', 'workmates_get_group_invites_template', 10, 2 );
