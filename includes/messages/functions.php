<?php
/**
 * WorkMates Messages functions.
 *
 * @package WorkMates
 * @since  2.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Make sure it is possible to send messages to any member.
 *
 * @since 2.0.0
 */
function workmates_messages_autocomplete_all() {
	buddypress()->messages->autocomplete_all = true;
}

/**
 * Search user count query Fallback in case xprofile is not activated
 *
 * @package WorkMates
 * @since 1.0
 *
 * @param  string $sql The SQL query to count the matching users.
 * @return string      Unchanged or filtered count query.
 */
function workmates_filter_message_ac_count( $sql = '' ) {
	global $wpdb;
	$bp = buddypress();

	$autocomplete = $bp->messages->autocomplete_all;

	if( ! bp_is_active( 'xprofile' ) && ! empty( $autocomplete ) ) {
		$search_terms = esc_sql( like_escape( $search_terms ) );
		$status_sql   = bp_core_get_status_sql( 'u.' );
		$like = $wpdb->prepare( "LIKE %s", '%'.$search_terms.'%' );

		$sql = "SELECT COUNT(DISTINCT u.ID) as id FROM {$wpdb->users} u WHERE {$status_sql} AND ( u.user_email {$like} OR u.display_name {$like} OR u.user_nicename {$like}  ) ORDER BY u.display_name ASC";
	}

	return apply_filters( 'workmates_filter_message_ac_count', $sql );
}

/**
 * Search user select query Fallback in case xprofile is not activated
 *
 * @package WorkMates
 * @since 1.0
 *
 * @param  string  $sql          The SQL query to select the matching users.
 * @param  string  $search_terms The autocomplete search characters.
 * @param  integer $pag_sql      The SQL offset.
 * @return string                Unchanged or the filtered select query.
 */
function workmates_filter_message_ac_select( $sql = '', $search_terms = '', $pag_sql = 1 ) {
	global $wpdb;
	$bp = buddypress();

	$autocomplete = $bp->messages->autocomplete_all;

	if( ! bp_is_active( 'xprofile' ) && ! empty( $autocomplete ) ) {
		$search_terms = esc_sql( like_escape( $search_terms ) );
		$status_sql   = bp_core_get_status_sql( 'u.' );
		$like = $wpdb->prepare( "LIKE %s", '%'.$search_terms.'%' );

		$sql = "SELECT DISTINCT u.ID as id, u.user_registered, u.user_nicename, u.user_login, u.user_email FROM {$wpdb->users} u WHERE {$status_sql} AND ( u.user_email {$like} OR u.display_name {$like} OR u.user_nicename {$like}  ) ORDER BY u.display_name ASC{$pag_sql}";
	}

	return apply_filters( 'workmates_filter_message_ac_select', $sql, $search_terms, $pag_sql );
}

/**
 * Filter BP_User_Query::populate_extras to override each queries users fullname
 *
 * Replaces bp_xprofile_filter_user_query_populate_extras if BuddyPress xprofile is not active
 *
 * @package WorkMates
 * @since 1.0
 *
 * @param BP_User_Query $user_query
 * @param string $user_ids_sql
 */
function workmates_user_query_include_fullnames( BP_User_Query $user_query, $user_ids_sql ) {

	// BuddyPress xprofile is handling it
	// But if not activated, as invites_template
	// uses fullname, this function avoids
	// the notice error.
	if ( bp_is_active( 'xprofile' ) ) {
		return;
	}

	$user_id_names = bp_core_get_user_displaynames( $user_query->user_ids );

	// Loop through names and override each user's fullname
	foreach ( $user_id_names as $user_id => $user_fullname ) {
		if ( isset( $user_query->results[ $user_id ] ) ) {
			$user_query->results[ $user_id ]->fullname = $user_fullname;
		}
	}
}
