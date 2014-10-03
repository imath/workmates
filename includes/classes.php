<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WorkMates_Group_Invite_Query' ) ) :
/**
 * WorkMates Group invite Class
 *
 * @since WorkMates (1.0)
 */
class WorkMates_Group_Invite_Query extends BP_User_Query {
	/**
	 * Array of group member ids, cached to prevent redundant lookups
	 *
	 * @var null|array Null if not yet defined, otherwise an array of ints
	 * @package WorkMates
	 * @since 1.0
	 */
	protected $group_member_ids;

	/**
	 * Set up action hooks
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	public function setup_hooks() {

		add_action( 'bp_pre_user_query_construct', array( $this, 'build_exclude_args' ) );

		if ( ! bp_is_active( 'xprofile' ) ) {
			add_action( 'bp_pre_user_query', array( $this, 'search_noprofile_fallback' ), 10, 1 );
		}
	}

	/**
	 * Exclude group members from the user query
	 * as it's not needed to invite members to join the group
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	public function build_exclude_args() {
		$this->query_vars = wp_parse_args( $this->query_vars, array(
			'group_id'     => 0,
		) );

		$group_member_ids = $this->get_group_member_ids();

		if ( !empty( $group_member_ids ) ) {
			$this->query_vars['exclude'] = $group_member_ids;
		}
	}

	/**
	 * Get the members of the queried group
	 *
	 * @package WorkMates
	 * @since 1.0
	 *
	 * @return array $ids User IDs of relevant group member ids
	 */
	protected function get_group_member_ids() {
		global $wpdb;

		if ( is_array( $this->group_member_ids ) ) {
			return $this->group_member_ids;
		}

		$bp  = buddypress();
		$sql = array(
			'select'  => "SELECT user_id FROM {$bp->groups->table_name_members}",
			'where'   => array(),
			'orderby' => '',
			'order'   => '',
			'limit'   => '',
		);

		/** WHERE clauses *****************************************************/

		// Group id
		$sql['where'] = $wpdb->prepare( "WHERE group_id = %d", $this->query_vars['group_id'] );

		/** ORDER BY clause ***************************************************/
		$sql['orderby'] = "ORDER BY date_modified";
		$sql['order']   = "DESC";

		/** LIMIT clause ******************************************************/
		$this->group_member_ids = $wpdb->get_col( "{$sql['select']} {$sql['where']} {$sql['orderby']} {$sql['order']} {$sql['limit']}" );

		return $this->group_member_ids;
	}

	/**
	 * Fallback in case xprofile is not active
	 * to allow searching members
	 *
	 * @package WorkMates
	 * @since 1.0
	 */
	public function search_noprofile_fallback( $user_query = null ) {
		global $wpdb;

		if ( empty( $user_query ) || ( ! empty( $user_query->uid_clauses['where'] ) && preg_match( '/user_nicename/', $user_query->uid_clauses['where'] ) ) ) {
			return;
		}

		$context = false;

		if ( ! empty( $user_query->query_vars['context'] ) ) {
			$context = $user_query->query_vars['context'];
		}

		$search_terms = false;

		if ( ! empty( $user_query->query_vars['search_terms'] ) ) {
			$search_terms = $user_query->query_vars['search_terms'];
		}

		if ( empty( $context ) || $context != 'workmates' || empty( $search_terms ) ) {
			return;
		}

		$like = $wpdb->prepare( "LIKE %s", '%'.$search_terms.'%' );

		$user_query->uid_clauses['where'] .= " AND ( u.user_email {$like} OR u.display_name {$like} OR u.user_nicename {$like} )";
	}
}

endif;
