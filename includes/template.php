<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

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

		if( empty( $group ) )
			return false;

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