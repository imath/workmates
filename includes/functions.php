<?php
/**
 * WorkMates functions.
 *
 * @package WorkMates
 * @since  1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/* global functions */

/**
 * Returns plugin version
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_plugin_version() {
	return workmates()->version;
}

/**
 * Returns plugin's dir
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_plugin_dir() {
	return workmates()->plugin_dir;
}

/**
 * Returns plugin's includes dir
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_includes_dir() {
	return workmates()->includes_dir;
}

/**
 * Returns plugin's includes url
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_includes_url() {
	return workmates()->includes_url;
}

/**
 * Returns plugin's js url
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_js_url() {
	return workmates()->plugin_js;
}

/**
 * Returns plugin's js url
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses workmates() plugin's main instance
 */
function workmates_get_css_url() {
	return workmates()->plugin_css;
}

/**
 * Add the Group Invite messages to the Groups Loop into the User's invites sceen.
 *
 * @since  2.0.0
 *
 * @param  boolean            $has_groups True if Groups were found. False otherwise.
 * @param  BP_Groups_Template $g_template The Groups loop Object
 * @param  array              $r          The Loop's queried arguments.
 * @return boolean                        True if Groups were found. False otherwise.
 */
function workmates_attach_group_invites_message( $has_groups = true, BP_Groups_Template $g_template, $r = array() ) {
	if ( ! isset( $r['type'] ) || 'invites' !== $r['type'] || empty( $g_template->groups ) ) {
		return $has_groups;
	}

	global $wpdb, $groups_template;
	$bp = buddypress();

	$gids = wp_list_pluck( $g_template->groups, 'id' );
	$group_list = implode( ',', wp_parse_id_list( $gids ) );
	$where = array(
		"group_id IN ({$group_list})",
		$wpdb->prepare( 'user_id = %d AND is_confirmed = 0', bp_loggedin_user_id() ),
	);

	$messages = $wpdb->get_results( "SELECT group_id, comments FROM {$bp->groups->table_name_members} WHERE " . implode( ' AND ', $where ), OBJECT_K );

	foreach ( $groups_template->groups as $k => $group ) {
		$g = (int) $group->id;

		if ( ! isset( $messages[$g] ) ) {
			continue;
		}

		$groups_template->groups[$k]->invite_message = $messages[$g]->comments;
	}

	return $has_groups;
}
add_filter( 'bp_has_groups', 'workmates_attach_group_invites_message', 10, 3 );

/**
 * Outputs the Invite message into the User's group invites loop.
 *
 * @since  2.0.0
 */
function workmates_output_group_invites_message() {
	global $groups_template;

	if ( empty( $groups_template->group->invite_message ) ) {
		return;
	}

	printf( '<h5>%1$s</h5><p>%2$s</p>',
		__( 'Message d\'invitation', 'workmates' ),
		wp_kses( $groups_template->group->invite_message, array() )
	);
}
add_action( 'bp_group_invites_item', 'workmates_output_group_invites_message' );

/**
 * Saves the invite message into the Group Members table.
 *
 * @since  2.0.0
 *
 * @param  BP_Groups_Member $group_member The group member object.
 */
function workmates_group_invites_save_message( BP_Groups_Member $group_member ) {
	$bp = buddypress();

	if ( isset( $bp->groups->invites_message ) ) {
		$group_member->comments = $bp->groups->invites_message;
	}
}
add_filter( 'groups_member_before_save', 'workmates_group_invites_save_message', 10, 1 );

/**
 * Makes sure the Group Invites message is appended to the email sent.
 *
 * @since  2.0.0
 *
 * @param  WP_Post  $post  The post object containing html & plain email text.
 * @param  BP_Email $email The email (object) about to be sent.
 * @return WP_Post         The post object containing html & plain email text.
 */
function workmates_group_invites_set_message( WP_Post $post, BP_Email $email ) {
	$bp = buddypress();

	if ( empty( $bp->groups->invites_message ) || ! $post->ID ) {
		return $post;
	}

	$email_types = wp_get_object_terms( $post->ID, bp_get_email_tax_type(), array( 'fields' => 'names' ) );
	$email_type  = reset( $email_types );

	if ( 'groups-invitation' !== $email_type ) {
		return $post;
	}

	$message = sprintf( __( 'Message d’invitation : %s', 'workmates' ), wp_strip_all_tags( $bp->groups->invites_message ) );
	$post->post_content .= "\n" . $message;
	$post->post_excerpt .= "\n\n" . $message;

	return $post;
}
add_filter( 'bp_email_set_post_object', 'workmates_group_invites_set_message', 10, 2 );

if ( ! function_exists( 'bp_nouveau_single_item_subnav_classes' ) ) :
/**
 * Makes sure the function exists as it is used in the invites JS Template.
 *
 * @since 2.0.0
 */
function bp_nouveau_single_item_subnav_classes() {
	echo 'group-subnav bp-invites-nav';
}
endif;

if ( ! function_exists( 'bp_nouveau_search_default_text' ) ) :
/**
 * Makes sure the function exists as it is used in the invites JS Template.
 *
 * @since 2.0.0
 */
function bp_nouveau_search_default_text() {
	esc_html_e( 'Rechercher un membre', 'workmates' );
}
endif;

/**
 * Wraper to toggle return 0 in filters.
 *
 * @since  2.0.0
 *
 * @return integer 0
 */
function workmates_return_zero() {
	return __return_zero();
}

/**
 * Temporarly replace the Legacy Stack by the Nouveau one.
 *
 * @since 2.0.0
 *
 * @param  array $stack A list of template locations.
 * @return array        A list of template locations.
 */
function workmates_alter_template_stack( $stack = array() ) {
	remove_filter( 'bp_get_template_stack', 'workmates_alter_template_stack', 10, 1 );

	$legacy_path  = untrailingslashit( bp_get_theme_compat_dir() );
	$nouveau_path = untrailingslashit( workmates()->tp_dir );

	foreach ( $stack as $key => $path ) {
		if ( 0 !== strpos( $path, $legacy_path ) ) {
			continue;
		}

		$stack[ $key ] = str_replace( $legacy_path, $nouveau_path, $path );
	}

	return $stack;
}

/**
 * Use the Group Invites JS Templates in the Group "Invite" create step.
 *
 * @since 2.0.0
 */
function workmates_get_group_create_invites_template() {
	add_filter( 'bp_get_template_stack', 'workmates_alter_template_stack', 10, 1 );
	remove_filter( 'bp_get_total_friend_count', 'workmates_return_zero' );

	bp_get_template_part( 'common/js-templates/invites/index' );
}

/**
 * Override the Send Invites template.
 *
 * @since 2.0.0
 *
 * @param  array  $templates The list of possible templates.
 * @param  string $slug      The requested template.
 * @return array             A new list of one specific template.
 */
function workmates_get_group_invites_template( $templates = array(), $slug = '' ) {
	if ( 'groups/single/send-invites' !== $slug && ! ( 'groups/create' === $slug && bp_is_group_creation_step( 'group-invites' ) ) ) {
		return $templates;
	}

	if ( bp_is_group_creation_step( 'group-invites' ) ) {
		add_filter( 'bp_get_total_friend_count', 'workmates_return_zero' );
		add_action( 'groups_custom_create_steps', 'workmates_get_group_create_invites_template' );
		return $templates;
	}

	add_filter( 'bp_get_template_stack', 'workmates_alter_template_stack', 10, 1 );

	return array( 'common/js-templates/invites/index.php' );
}
add_filter( 'bp_get_template_part', 'workmates_get_group_invites_template', 10, 2 );

/**
 * Override the POST object to allow member type filtering.
 *
 * @since  2.0.0
 */
function workmates_get_group_potential_member_type_invites() {
	if ( isset( $_POST['scope'] ) && 0 === strpos( $_POST['scope'], '_wm_mt_' ) ) {
		$_POST['member_type'] = str_replace( '_wm_mt_', '', $_POST['scope'] );
		$_POST['scope']       = 'members';
	}
}
add_action( 'wp_ajax_groups_get_group_potential_invites', 'workmates_get_group_potential_member_type_invites', 9 );

/**
 * Save the group invites setting for the displayed user.
 *
 * NB: This is only available if the Friends component is active.
 *
 * @since 2.0.0
 * @todo  move this into a file only loaded if the friends component is active
 */
function workmates_member_invites_visibility_handler() {
	if ( ! isset( $_POST['member-group-invites-submit'] ) ) {
		return;
	}

	bp_nouveau_groups_screen_invites_restriction();
}
add_action( 'bp_screens', 'workmates_member_invites_visibility_handler' );

/**
 * Output the form to let the user removes himself from the all members tab.
 *
 * NB: This is only available if the Friends component is active.
 *
 * @since 2.0.0
 * @todo  move this into a file only loaded if the friends component is active
 */
function workmates_member_invites_visibility_setting() {
	if ( ! bp_is_user() || ! bp_is_current_component( 'settings' ) || ! bp_is_current_action( 'invites' ) ) {
		return;
	}

	$group_invites_setting = (int) bp_get_user_meta( bp_displayed_user_id(), '_bp_nouveau_restrict_invites_to_friends' );
	?>
	<form action="<?php echo esc_url( bp_displayed_user_domain() . bp_get_settings_slug() . '/invites/' ); ?>" name="account-group-invites-form" id="account-group-invites-form" class="standard-form" method="post">

		<label for="account-group-invites-preferences">
			<input type="checkbox" name="account-group-invites-preferences" id="account-group-invites-preferences" value="1" <?php checked( 1, $group_invites_setting ); ?>/>
				<?php esc_html_e( 'Je souhaite restreindre les invitations de groupe à mes amis uniquement.', 'workmates' ); ?>
		</label>

		<div class="submit">
			<input type="submit" value="<?php esc_attr_e( 'Enregistrer les modifications', 'workmates' ); ?>" id="member-group-invites" name="member-group-invites-submit" />
		</div>

		<?php wp_nonce_field( 'bp_nouveau_group_invites_settings' ); ?>

	</form>
	<?php
}
add_action( 'bp_template_content', 'workmates_member_invites_visibility_setting' );
