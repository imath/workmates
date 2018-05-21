<?php
/**
 * WorkMates Hooks.
 *
 * @package WorkMates
 * @since  2.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Group Invites
if ( bp_is_active( 'groups' ) ) {
	add_action( 'bp_setup_theme',     'workmates_get_nouveau_messages_files'     );
	add_action( 'bp_enqueue_scripts', 'workmates_enqueue_nouveau_messages_cssjs' );

	add_action( 'groups_setup_nav',          'bp_nouveau_group_setup_nav'                         );
	add_filter( 'groups_create_group_steps', 'bp_nouveau_group_invites_create_steps',       10, 1 );
	add_action( 'admin_init',                'workmates_remove_group_invites_ajax_actions', 20    );

	add_filter( 'bp_has_groups',             'workmates_attach_group_invites_message', 10, 3 );
	add_action( 'bp_group_invites_item',     'workmates_output_group_invites_message'        );
	add_filter( 'groups_member_before_save', 'workmates_group_invites_save_message',   10, 1 );
	add_filter( 'bp_email_set_post_object',  'workmates_group_invites_set_message',    10, 2 );

	add_filter( 'bp_get_template_part', 'workmates_get_group_invites_template', 10, 2 );

	add_action( 'wp_ajax_groups_get_group_potential_invites', 'workmates_get_group_potential_member_type_invites', 9 );

	if ( bp_is_active( 'friends' ) ) {
		add_action( 'bp_settings_setup_nav', 'bp_nouveau_groups_invites_restriction_nav' );
		add_filter( 'bp_settings_admin_nav', 'bp_nouveau_groups_invites_restriction_admin_nav', 10, 1 );

		add_action( 'bp_screens',          'workmates_member_invites_visibility_handler' );
		add_action( 'bp_template_content', 'workmates_member_invites_visibility_setting' );
	}
} else {
	add_action( 'all_admin_notices', 'workmates_deactivate_notice' );
}

// Messages.
if ( bp_is_active( 'messages' ) ) {
	add_filter( 'bp_core_search_users_count_sql', 'workmates_filter_message_ac_count',      10, 1 );
	add_filter( 'bp_core_search_users_sql',       'workmates_filter_message_ac_select',     10, 3 );
	add_action( 'bp_user_query_populate_extras',  'workmates_user_query_include_fullnames',  2, 2 );

	if ( ! bp_is_active( 'friends' ) ) {
		add_action( 'bp_messages_setup_globals', 'workmates_messages_autocomplete_all' );
	}
}
