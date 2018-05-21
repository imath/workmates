<?php
/**
 * WorkMates deprecated functions.
 *
 * @package WorkMates
 * @since  2.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Returns plugin's includes url
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_includes_url() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns the available members (not invited and not member of the group) to WorkMates Editor
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_ajax_get_users() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	wp_send_json_error();
}

/**
 * Handles un-replied invites remove requests
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_ajax_uninvite_user() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	wp_send_json_error();
}

if ( ! class_exists( 'WorkMates_Group_Invite_Query' ) ) :
/**
 * WorkMates Group invite Class
 *
 * @since WorkMates (1.0)
 * @deprecated 2.0.0
 */
class WorkMates_Group_Invite_Query extends BP_User_Query {
	/**
	 * Set up action hooks
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function setup_hooks() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}
	/**
	 * Exclude group members from the user query
	 * as it's not needed to invite members to join the group
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function build_exclude_args() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}
	/**
	 * Get the members of the queried group
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @deprecated 2.0.0
	 *
	 * @return array $ids User IDs of relevant group member ids
	 */
	protected function get_group_member_ids() {
		_deprecated_function( __METHOD__, '2.0.0' );
		return $this->group_member_ids;
	}
	/**
	 * Fallback in case xprofile is not active
	 * to allow searching members
	 *
	 * @package WorkMates
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function search_noprofile_fallback( $user_query = null ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}
}
endif;

/**
 * Enqueues the WorkMates editor scripts, css, settings and strings
 *
 * Inspired by wp_enqueue_media()
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_enqueue_editor( $args = array() ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return;
}

/**
 * Trick to make the media-views works without plupload loaded
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_plupload_settings() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return;
}

/**
 * The template needed for the WorkMates editor
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_media_templates() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return;
}


/**
 * Returns plugin's component id
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_component_id() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns plugin's component slug
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_component_slug() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns plugin's component name
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_component_name() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns plugin's Group component name
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_group_component_name() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns plugin's Group component slug
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_group_component_slug() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Are we on the group invite workmates screen ?
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_is_group_front() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Are we on the group invite workmates create screen ?
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_is_group_create() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Prepare users before sending a json object to the WorkMates editor
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_prepare_user_for_js( $users ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * This is a copy of the BuddyPress function to send invites
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_group_invite() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

if ( class_exists( 'BP_Group_Extension' ) ) :
/**
 * The WorkMates Invites Group class
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
class WorkMates_Invites_Group extends BP_Group_Extension {
	/**
	 * construct method to add some settings and hooks
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	public function __construct() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Sends the invite while submitting on the display screen
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	public function setup_hooks() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}
	/**
	 * Create step for a group
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	public function create_screen( $group_id = null ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}
	/**
	 * Handling the create step for a group
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	public function create_screen_save( $group_id = null ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}
	/**
	 * Displays settings (not used)
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	public function edit_screen( $group_id = null ) {
		_deprecated_function( __METHOD__, '2.0.0' );
		return false;
	}
	/**
	 * Save the settings of the group (not used)
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	public function edit_screen_save( $group_id = null ) {
		_deprecated_function( __METHOD__, '2.0.0' );
		return $group_id;
	}
	/**
	 * Displays the form into the Group Admin Meta Box
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	public function admin_screen( $group_id = null ) {
		_deprecated_function( __METHOD__, '2.0.0' );
		return false;
	}
	/**
	 * Saves the settings from the Group Admin Meta Box
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	public function admin_screen_save( $group_id = null ) {
		_deprecated_function( __METHOD__, '2.0.0' );
		return $group_id;
	}
	/**
	 * Displays the WorkMates invites content of the group
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	public function display( $group_id = null ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}
	/**
	 * We do not use widgets
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * @deprecated 2.0.0
	 */
	function widget_display() {
		_deprecated_function( __METHOD__, '2.0.0' );
		return false;
	}

}

/**
 * Waits for bp_init hook before loading the group extension
 *
 * Let's make sure the group id is defined before loading our stuff
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_invites_register_group_extension() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}
endif;

/**
 * Fallback function in case of JS trouble
 *
 * As this plugin uses JS to add invites, this should not be used..
 * But who knows !
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_remove_group_invite() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Displays the invite form action
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_send_invites_action() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Builds the invite form action
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_send_invites_action() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Displays the remove link (no-js fallback)
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_invite_user_remove_invite_url() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Builds the remove link
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_invite_user_remove_invite_url() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Displays the invited user id
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_invite_item_id() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns the invited user id
 *
 * @package WorkMates
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_invite_item_id() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_is_inviter() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_user_invited_by() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_user_invited_by() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_user_invited_by_avatar() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * @since 1.0
 * @deprecated 2.0.0
 */
function workmates_get_user_invited_by_avatar() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}
