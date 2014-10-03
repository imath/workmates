<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fallback function in case of JS trouble
 *
 * As this plugin uses JS to add invites, this should not be used..
 * But who knows !
 *
 * @package WorkMates
 * @since 1.0
 */
function workmates_remove_group_invite() {

    if ( ! bp_is_single_item() ) {
    	return false;
    }

    if ( workmates_is_group_front() && bp_is_action_variable( 'remove', 0 ) && is_numeric( bp_action_variable( 1 ) ) ) {

    	if ( ! check_admin_referer( 'workmates_uninvite_user' ) ) {
    		return false;
    	}

    	$workmate_id =  intval( bp_action_variable( 1 ) );
    	$group_id = bp_get_current_group_id();
    	$message = __( 'Invite successfully removed', 'workmates' );
    	$redirect = wp_get_referer();
    	$error = false;

    	if ( ! bp_groups_user_can_send_invites( $group_id ) ) {
    		$message = __( 'You are not allowed to send or remove invites', 'workmates' );
    		$error = 'error';
    	} else if ( BP_Groups_Member::check_for_membership_request( $workmate_id, $group_id ) ) {
    		$message = __( 'The member requested to join the group.', 'workmates' );
    		$error = 'error';
    	} else if ( ! groups_uninvite_user( $workmate_id, $group_id ) ) {
    		$message = __( 'There was an error removing the invite', 'workmates' );
    		$error = 'error';
    	}

    	bp_core_add_message( $message, $error );
    	bp_core_redirect( $redirect );
    }
}
add_action( 'bp_screens', 'workmates_remove_group_invite' );
