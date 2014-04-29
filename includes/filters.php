<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Search user count query Fallback in case xprofile is not activated
 * 
 * @package WorkMates
 * @since 1.0
 * 
 * @uses buddypress() to get BuddyPress main instance
 * @uses bp_is_active() to check the component is active
 * @return string the filtered count query
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
 * @uses buddypress() to get BuddyPress main instance
 * @uses bp_is_active() to check the component is active
 * @return string the filtered select query
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
	if ( bp_is_active( 'xprofile' ) )
		return;

	$user_id_names = bp_core_get_user_displaynames( $user_query->user_ids );

	// Loop through names and override each user's fullname
	foreach ( $user_id_names as $user_id => $user_fullname ) {
		if ( isset( $user_query->results[ $user_id ] ) ) {
			$user_query->results[ $user_id ]->fullname = $user_fullname;
		}
	}
}
add_action( 'bp_user_query_populate_extras', 'workmates_user_query_include_fullnames', 2, 2 );
