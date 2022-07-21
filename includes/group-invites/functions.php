<?php
/**
 * WorkMates Group Invites functions.
 *
 * @package WorkMates
 * @since  2.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Include BP Nouveau files.
 *
 * @since 2.0.0
 */
function workmates_get_nouveau_messages_files() {
	$bp = buddypress();
	$wm = workmates();

	if ( ! isset( $bp->theme_compat->packages['nouveau'] ) ) {
		add_action( 'all_admin_notices', array( $this, 'deactivate_notice' ) );
		return;
	}

	$wm->tp_url = trailingslashit( $bp->theme_compat->packages['nouveau']->__get( 'url' ) );
	$wm->tp_dir = trailingslashit( $bp->theme_compat->packages['nouveau']->__get( 'dir' ) );

	// Include needed BP Nouveau files
	require $wm->tp_dir . 'includes/groups/classes.php';
	require $wm->tp_dir . 'includes/groups/functions.php';

	if ( wp_doing_ajax() ) {
		require $wm->tp_dir . 'includes/groups/ajax.php';
	}
}

/**
 * Register/Enqueues JS/CSS asset for the Nouveau Group Invites UI.
 *
 * @since 2.0.0
 */
function workmates_enqueue_nouveau_messages_cssjs() {
	if ( ! bp_is_group_invites() && ! ( bp_is_group_create() && bp_is_group_creation_step( 'group-invites' ) ) ) {
		return;
	}

	// Get script.
	$scripts = bp_nouveau_groups_register_scripts( array(
		'bp-nouveau' => array(),
	) );

	// Bail if no scripts
	if ( ! isset( $scripts['bp-nouveau-group-invites'] ) ) {
		return;
	}

	$min = workmates_min_suffix();

	wp_enqueue_style(
		'workmates-group-invites',
		sprintf( '%1$sstyle%2$s.css', workmates_get_css_url(), $min ),
		array( 'dashicons' ),
		workmates_get_plugin_version()
	);

	$script = $scripts['bp-nouveau-group-invites'];
	array_shift( $script['dependencies'] );

	wp_register_script(
		'bp-nouveau-group-invites',
		workmates()->tp_url . '/' . sprintf( $script['file'], $min ),
		$script['dependencies'],
		workmates_get_plugin_version(),
		$script['footer']
	);

	wp_enqueue_script(
		'workmates-group-invites',
		sprintf( '%1$sscript%2$s.js', workmates_get_js_url(), $min ),
		array( 'bp-nouveau-group-invites' ),
		workmates_get_plugin_version(),
		true
	);

	$localized_data = bp_nouveau_groups_localize_scripts( array(
		'nonces' => array(
			'groups' => wp_create_nonce( 'bp_nouveau_groups' ),
		) )
	);

	// Use member types to add new filters to the Group Invites UI.
	$member_types = bp_get_member_types( array(), 'objects' );
	$order        = 5;

	if ( ! empty( $member_types ) && is_array( $member_types ) ) {
		foreach ( $member_types as $type_key => $type ) {
			$order += 1;

			$localized_data['group_invites']['nav'][] = array(
				'id'      => '_wm_mt_' . $type_key,
				'caption' => esc_html( $type->labels['name'] ),
				'order'   => $order,
			);
		}

		// Reorder the nav !
		$localized_data['group_invites']['nav'] = bp_sort_by_key(
			$localized_data['group_invites']['nav'],
			'order',
			'num'
		);
	}

	wp_localize_script( 'bp-nouveau-group-invites', 'BP_Nouveau', $localized_data );
}

/**
 * Remove some Nouveau AJAX actions to prevent conflicts with Legacy.
 *
 * @since 2.0.0
 */
function workmates_remove_group_invites_ajax_actions() {
	$ajax_actions = array(
		array( 'groups_filter'             => array( 'function' => 'bp_nouveau_ajax_object_template_loader', ) ),
		array( 'groups_join_group'         => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        ) ),
		array( 'groups_leave_group'        => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        ) ),
		array( 'groups_request_membership' => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        ) ),
	);

	foreach ( $ajax_actions as $ajax_action ) {
		$action = key( $ajax_action );

		remove_action( 'wp_ajax_' . $action, $ajax_action[ $action ]['function'] );
	}
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
function workmates_attach_group_invites_message( $has_groups = true, BP_Groups_Template $g_template = null, $r = array() ) {
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

/**
 * Save the group invites setting for the displayed user.
 *
 * NB: This is only available if the Friends component is active.
 *
 * @since 2.0.0
 */
function workmates_member_invites_visibility_handler() {
	if ( ! isset( $_POST['member-group-invites-submit'] ) ) {
		return;
	}

	bp_nouveau_groups_screen_invites_restriction();
}

/**
 * Output the form to let the user removes himself from the all members tab.
 *
 * NB: This is only available if the Friends component is active.
 *
 * @since 2.0.0
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
