<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'BP_Group_Extension' ) ) :
/**
 * The WorkMates Invites Group class
 *
 * @package WorkMates
 * @since 1.0
 * 
 * @see http://codex.buddypress.org/developer/group-extension-api/
 */
class WorkMates_Invites_Group extends BP_Group_Extension {	
	

	/**
	 * construct method to add some settings and hooks
	 * 
	 * @package WorkMates
 	 * @since 1.0
	 *
	 * @uses workmates_get_group_component_slug() to get the plugin name
	 * @uses workmates_get_group_component_name() to get the plugin slug
	 */
	public function __construct() {

		$this->setup_hooks();

		$args = array(
        	'slug'              => workmates_get_group_component_slug(),
       		'name'              => workmates_get_group_component_name(),
       		'visibility'        => 'private',
       		'nav_item_position' => 91,
       		'enable_nav_item'   => bp_groups_user_can_send_invites(),
       		'screens'           => array( 
       								'create' => array(
       									'enabled' => true,
       								),
       								'admin' => array( 
       									'enabled' => false,
       								),
       								'edit' => array(
       									'enabled' => false,
       								)
       							)
    	);
    
    	parent::init( $args );
		
	}

	/**
	 * Sends the invite while submitting on the display screen
	 * 
	 * @package WorkMates
 	 * @since 1.0
 	 * 
	 * @uses  bp_is_action_variable() to check send action is requested
	 * @uses  workmates_is_group_front() to check we're on workmates invite screen
	 */
	public function setup_hooks() {
		if( bp_is_action_variable( 'send', 0 ) && workmates_is_group_front() )
			add_action( 'bp_actions', 'workmates_group_invite' );
	}

	/**
	 * Create step for a group
	 * 
	 * @package WorkMates
 	 * @since 1.0
 	 * 
	 * @param  integer $group_id group id
	 */
	public function create_screen( $group_id = null ) {
		/* If we're not at this step, go bye bye */
		if ( !bp_is_group_creation_step( $this->slug ) )
			return false;

		$this->display( $group_id );

		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

	/**
	 * Handling the create step for a group
	 * 
	 * @package WorkMates
 	 * @since 1.0
	 * 
	 * @param integer $group_id the group id
	 * @uses bp_get_new_group_id() to get the group id while in create step
	 * @uses bp_get_current_group_id() to get the group id
	 * @uses check_admin_referer() for security reasons
	 * @uses groups_invite_user() to store invites
	 * @uses bp_group_has_invites() to check for invites
	 * @uses groups_send_invites() to finally send the invites
	 */
	public function create_screen_save( $group_id = null ) {
		if( empty( $group_id ) )
			$group_id = bp_get_new_group_id() ? bp_get_new_group_id() : bp_get_current_group_id();

		/* Always check the referer */
		check_admin_referer( 'groups_create_save_' . $this->slug );

		if ( !empty( $_POST['workmates'] ) ) {
			foreach( (array) $_POST['workmates'] as $workmate ) {
				groups_invite_user( array( 'user_id' => $workmate, 'group_id' => $group_id ) );
			}
		}

		/* Send invites if any */
		if ( bp_group_has_invites() ) 
			groups_send_invites( bp_loggedin_user_id(), $group_id );
		
	}

	/**
	 * Displays settings (not used)
	 *
	 * @package WorkMates
 	 * @since 1.0
 	 * 
 	 * @param  integer $group_id group id
	 */
	public function edit_screen( $group_id = null ) {
		return false;
	}


	/**
	 * Save the settings of the group (not used)
	 * 
	 * @package WorkMates
 	 * @since 1.0
 	 * 
 	 * @param  integer $group_id group id
	 */
	public function edit_screen_save( $group_id = null ) {
		return $group_id;
	}

	/**
	 * Displays the form into the Group Admin Meta Box
	 * 
	 * @package WorkMates
 	 * @since 1.0
	 * 
	 * @param  integer $group_id group id
	 */
	public function admin_screen( $group_id = null ) {
		return false;
	}

	/**
	 * Saves the settings from the Group Admin Meta Box
	 *
	 * @package WorkMates
 	 * @since 1.0
	 * 
	 * @param integer $item_id the group id
	 */
	public function admin_screen_save( $group_id = null ) {
		return $group_id;
	}

	/**
	 * Displays the WorkMates invites content of the group
	 * 
	 * @package WorkMates
 	 * @since 1.0
	 *
	 * @param  integer $group_id group id
	 * @uses bp_get_new_group_id() to get the group id while in create step
	 * @uses bp_get_current_group_id() to get the group id
	 * @uses workmates_is_group_create() to check for the create step
	 * @uses workmates_send_invites_action() to build the form action
	 * @uses the group invite loop to diplay unreplied invites
	 * @uses workmates_invite_user_remove_invite_url() to build the no-js remove link
	 * @uses workmates_invite_item_id() to get the invite item id
	 * @uses wp_nonce_field() for security reasons
	 * @return string html output
	 */
	public function display( $group_id = null ) {
		if( empty( $group_id ) )
			$group_id = bp_get_new_group_id() ? bp_get_new_group_id() : bp_get_current_group_id();

		if( ! workmates_is_group_create() ):
		?>
		
		<form action="<?php workmates_send_invites_action();?>" method="post" id="send-invite-form" class="standard-form" role="main">

		<?php endif;?>

			<h3><a href="#" class="button" id="workmates-select"><?php _e( 'Select Workmates', 'workmates' );?></a></h3>

			<ul id="workmates-list" class="item-list">
			<?php if ( bp_group_has_invites() ) : ?>

				<?php while ( bp_group_invites() ) : bp_group_the_invite(); ?>

					<li class="workmates-invited" id="<?php bp_group_invite_item_id(); ?>">
						<?php bp_group_invite_user_avatar(); ?>

						<h4><?php bp_group_invite_user_link(); ?></h4>
						<span class="activity"><?php bp_group_invite_user_last_active(); ?></span>

						<?php do_action( 'bp_group_send_invites_item' ); ?>

						<div class="action">
							<a class="button workmates-remove" href="<?php workmates_invite_user_remove_invite_url(); ?>" id="<?php bp_group_invite_item_id(); ?>" data-id="<?php workmates_invite_item_id();?>"><?php _e( 'Remove Invite', 'workmates' ); ?></a>

							<?php do_action( 'bp_group_send_invites_item_action' ); ?>
						</div>
					</li>

				<?php endwhile; ?>

			<?php endif; ?>
			</ul><!-- #friend-list -->

			<?php do_action( 'bp_after_group_send_invites_list' ); ?>

			<div class="clear"></div>


			<?php wp_nonce_field( 'workmates_send_invites', '_wpnonce_send_invites' ); ?>

		<?php if( ! workmates_is_group_create() ): ?>

			<input type="hidden" name="group_id" id="group_id" value="<?php echo $group_id; ?>" />

			<div class="submit">
				<input type="submit" name="submit" id="submit" value="<?php _e( 'Send Invites', 'workmates' ); ?>" />
			</div>

		</form><!-- #send-invite-form -->
		
		<?php
		endif;
	}


	/**
	 * We do not use widgets
	 * 
	 * @package WorkMates
 	 * @since 1.0
	 * 
	 * @return boolean false
	 */
	function widget_display() {
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
 * 
 * @uses bp_register_group_extension() to register the group extension
 */
function workmates_invites_register_group_extension() {
	bp_register_group_extension( 'WorkMates_Invites_Group' );
}

add_action( 'bp_init', 'workmates_invites_register_group_extension' );

endif;