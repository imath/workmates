<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Displays the invite form action
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates_get_send_invites_action() to get it
 */
function workmates_send_invites_action() {
	echo workmates_get_send_invites_action();
}

	/**
	 * Builds the invite form action
	 *
	 * @package WorkMates
	 * @since 1.0
	 *
	 * @uses groups_get_current_group() to get current group
	 * @uses bp_get_group_permalink() to build the group link
	 * @uses workmates_get_group_component_slug() to get the plugin's group invite workmates slug
	 * @return string the invite form action
	 */
	function workmates_get_send_invites_action() {
		$group = groups_get_current_group();

		if ( empty( $group ) ) {
			return false;
		}

		return apply_filters( 'workmates_get_send_invites_action', bp_get_group_permalink( $group ) . workmates_get_group_component_slug() . '/send/' );
	}

/**
 * Displays the remove link (no-js fallback)
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates_get_invite_user_remove_invite_url() to get it
 */
function workmates_invite_user_remove_invite_url() {
	echo workmates_get_invite_user_remove_invite_url();
}

	/**
	 * Builds the remove link
	 *
	 * @package WorkMates
	 * @since 1.0
	 *
	 * @return string the invite form action
	 */
	function workmates_get_invite_user_remove_invite_url() {
		global $invites_template;

		$uninvite_url = bp_is_current_action( 'create' ) ? bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/create/step/' . workmates_get_group_component_slug() . '/?user_id='. $invites_template->invite->user->id : bp_get_group_permalink( groups_get_current_group() ) . workmates_get_group_component_slug() . '/remove/' . $invites_template->invite->user->id;

		return wp_nonce_url( $uninvite_url, 'workmates_uninvite_user' );
	}

/**
 * Displays the invited user id
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates_get_invite_item_id() to get it
 */
function workmates_invite_item_id() {
	echo workmates_get_invite_item_id();
}

	/**
	 * Returns the invited user id
	 *
	 * @package WorkMates
	 * @since 1.0
	 *
	 * @return string the invited user id
	 */
	function workmates_get_invite_item_id() {
		global $invites_template;

		return apply_filters( 'workmates_get_invite_item_id', $invites_template->invite->user->id );
	}

function workmates_is_inviter() {
	return (bool) ( bp_loggedin_user_id() == workmates_get_user_invited_by() );
}

function workmates_user_invited_by() {
	echo workmates_get_user_invited_by();
}
	function workmates_get_user_invited_by() {
		global $invites_template;

		return apply_filters( 'bp_get_group_invite_user_last_active', $invites_template->invite->user->inviter_id );
	}

function workmates_user_invited_by_avatar() {
	echo workmates_get_user_invited_by_avatar();
}

	function workmates_get_user_invited_by_avatar() {
		$inviter_id = workmates_get_user_invited_by();

		$avatar = bp_core_fetch_avatar( array( 'object' => 'user', 'item_id' => $inviter_id,  'width' => 20, 'height' => 20 ) );

		$output = esc_html__( 'Invited by:', 'workmates' );

		$output .= '<div><a href="' . bp_core_get_user_domain( $inviter_id ) . '" title="' . esc_attr( bp_core_get_username( $inviter_id ) ) . '">' . $avatar . '</a></div>';

		return apply_filters( 'workmates_get_user_invited_by_avatar', $output );
	}
