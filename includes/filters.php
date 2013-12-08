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

