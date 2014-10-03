<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns the available members (not invited and not member of the group) to WorkMates Editor
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses check_ajax_referer() for security reasons
 * @uses wp_parse_args() to merge args with defaults
 * @uses bp_get_current_group_id() to get current group id
 * @uses wp_send_json_error() to return an error to WorkMates Editor
 * @uses WorkMates_Group_Invite_Query() to build the invite users list
 * @uses wp_send_json_success() to return json respons to WorkMates Editor
 * @return string the json response
 */
function workmates_ajax_get_users() {

	check_ajax_referer( 'workmates-editor' );

	$query_args = array();

	if ( isset( $_REQUEST['query'] ) ) {
		$query_args = (array) $_REQUEST['query'];
	}

	$args = wp_parse_args( $query_args, array(
		'group_id'     => bp_get_current_group_id(),
		'type'         => 'alphabetical',
		'per_page'     => 20,
		'page'         => 1,
		'search_terms' => false,
		'context'      => 'workmates',
	) );

	if ( empty( $args['group_id'] ) ) {
		wp_send_json_error( __( 'Undefined group..', 'workmates' ) );
	}

	$query = new WorkMates_Group_Invite_Query( $args );

	$response = new stdClass();

	$response->meta = array( 'total_page' => 0, 'current_page' => 0 );

	if ( empty( $query->results ) ) {
		wp_send_json_error( $response );
	}

	$users = array_map( 'workmates_prepare_user_for_js', array_values( $query->results ) );
	$users = array_filter( $users );

	if ( ! empty( $args['per_page'] ) ) {
		$response->meta = array(
			'total_page' => ceil( (int) $query->total_users / (int) $args['per_page'] ),
			'current_page' => (int) $args['page'],
		);
	}

	$response->items = $users;

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_workmates_get_users', 'workmates_ajax_get_users' );

/**
 * Handles un-replied invites remove requests
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses check_ajax_referer() for security reasons
 * @uses wp_send_json_error() to return an error to WorkMates Editor
 * @uses BP_Groups_Member::check_for_membership_request() to check if the invited user has requested a membership
 * @uses groups_uninvite_user() to uninvite the user
 * @uses wp_send_json_success() to return json respons to WorkMates Editor
 * @return string the json response
 */
function workmates_ajax_uninvite_user() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	check_ajax_referer( 'workmates-editor' );

	if ( ! $_POST['workmate_id'] || ! $_POST['group_id'] ) {
		wp_send_json_error( '<div id="message" class="error"><p>' . __( 'User or Group id undefined', 'workmates' ) . '</p></div>' );
	}

	if ( ! bp_groups_user_can_send_invites( $_POST['group_id'] ) ) {
		wp_send_json_error( '<div id="message" class="error"><p>' . __( 'You do not have permission to send or delete invites', 'workmates' ) . '</p></div>' );
	}

	$group_id    = (int) $_POST['group_id'];
	$workmate_id = (int) $_POST['workmate_id'];

	// Users who have previously requested membership should not
	// have their requests deleted on the "uninvite" action
	if ( BP_Groups_Member::check_for_membership_request( $workmate_id, $group_id ) ) {
		wp_send_json_error( '<div id="message" class="error"><p>' . __( 'User is already member of this group.', 'workmates' ) . '</p></div>' );
	}

	// Remove the unsent invitation
	if ( ! groups_uninvite_user( $workmate_id, $group_id ) ) {
		wp_send_json_error( '<div id="message" class="error"><p>' . __( 'Something went wrong.', 'workmates' ) . '</p></div>' );
	}

	wp_send_json_success( '<div id="message" class="updated"><p>' . __( 'Invite successfully removed', 'workmates' ) . '</p></div>' );

}
add_action( 'wp_ajax_workmates_uninvite_user', 'workmates_ajax_uninvite_user' );
