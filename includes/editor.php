<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Enqueues the WorkMates editor scripts, css, settings and strings
 *
 * Inspired by wp_enqueue_media()
 *
 * @package WorkMates
 * @since 1.0
 *
 * @uses bp_get_new_group_id() to get the group id while in create step
 * @uses bp_get_current_group_id() to get the group id
 * @uses wp_parse_args() to merge args with defaults
 * @uses bp_get_option() to get the settings for the blog where BuddyPress runs
 * @uses add_query_arg() to add arguments to an url
 * @uses wp_create_nonce() for security reasons
 * @uses wp_localize_script() to attach the settings and strings in the page
 * @uses wp_enqueue_script() to safely add our script to WordPress queue
 * @uses wp_enqueue_style() to safely add our style to WordPress queue
 * @uses workmates_plupload_settings() to simulate plupload without loading it
 */
function workmates_enqueue_editor( $args = array() ) {

	// Enqueue me just once per page, please.
	if ( did_action( 'workmates_enqueue_editor' ) )
		return;

	$default_group_id = bp_get_new_group_id() ? bp_get_new_group_id() : bp_get_current_group_id();

	$defaults = array(
		'post' => null,
		'group_id' => $default_group_id,
	);
	$args = wp_parse_args( $args, $defaults );

	// We're going to pass the old thickbox media tabs to `media_upload_tabs`
	// to ensure plugins will work. We will then unset those tabs.
	$tabs = array(
		// handler action suffix => tab label
		'type'     => '',
		'type_url' => '',
		'gallery'  => '',
		'library'  => '',
	);

	$tabs = apply_filters( 'media_upload_tabs', $tabs );
	unset( $tabs['type'], $tabs['type_url'], $tabs['gallery'], $tabs['library'] );

	$props = array(
		'link'  => bp_get_option( 'image_default_link_type' ), // db default is 'file'
		'align' => bp_get_option( 'image_default_align' ), // empty default
		'size'  => bp_get_option( 'image_default_size' ),  // empty default
	);

	$settings = array(
		'tabs'      => $tabs,
		'tabUrl'    => add_query_arg( array( 'chromeless' => true ), admin_url('admin-ajax.php') ),
		'mimeTypes' => false,
		'captions'  => ! apply_filters( 'disable_captions', '' ),
		'nonce'     => array(
			'sendToEditor' => wp_create_nonce( 'media-send-to-editor' ),
			'workmates'    => wp_create_nonce( 'workmates-editor' )
		),
		'post'    => array(
			'id' => 0,
		),
		'defaultProps' => $props,
		'embedExts'    => false,
	);

	$post = $hier = null;
	$settings['group'] = intval( $args['group_id'] );

	// Do we have member types ?
	$workmates_member_types = array();
	$member_types = bp_get_member_types( array(), 'objects' );
	if ( ! empty( $member_types ) && is_array( $member_types ) ) {
		$workmates_member_types['wmMemberTypesAll'] = esc_html__( 'All member types', 'workmates' );
		foreach ( $member_types as $type_key => $type ) {
			$workmates_member_types['wmMemberTypes'][] = array( 'type' => $type_key, 'text' => esc_html( $type->labels['singular_name'] ) );
		}
	}

	if ( ! empty( $workmates_member_types ) ) {
		$settings = array_merge( $settings, $workmates_member_types );
	}

	$strings = array(
		// Generic
		'url'         => __( 'URL', 'workmates' ),
		'addMedia'    => __( 'Add Media', 'workmates' ),
		'search'      => __( 'Search', 'workmates' ),
		'select'      => __( 'Select', 'workmates' ),
		'cancel'      => __( 'Cancel', 'workmates' ),
		/* translators: This is a would-be plural string used in the media manager.
		   If there is not a word you can use in your language to avoid issues with the
		   lack of plural support here, turn it into "selected: %d" then translate it.
		 */
		'selected'    => __( '%d selected', 'workmates' ),
		'dragInfo'    => __( 'Drag and drop to reorder images.', 'workmates' ),

		// Upload
		'uploadFilesTitle'  => __( 'Upload Files', 'workmates' ),
		'uploadImagesTitle' => __( 'Upload Images', 'workmates' ),

		// Library
		'mediaLibraryTitle'  => __( 'Media Library', 'workmates' ),
		'insertMediaTitle'   => __( 'Insert Media', 'workmates' ),
		'createNewGallery'   => __( 'Create a new gallery', 'workmates' ),
		'returnToLibrary'    => __( '&#8592; Return to library', 'workmates' ),
		'allMediaItems'      => __( 'All media items', 'workmates' ),
		'noItemsFound'       => __( 'No items found.', 'workmates' ),
		'insertIntoPost'     => $hier ? __( 'Insert into page', 'workmates' ) : __( 'Insert into post', 'workmates' ),
		'uploadedToThisPost' => $hier ? __( 'Uploaded to this page', 'workmates' ) : __( 'Uploaded to this post', 'workmates' ),
		'warnDelete' =>      __( "You are about to permanently delete this item.\n  'Cancel' to stop, 'OK' to delete.", 'workmates' ),

		// From URL
		'insertFromUrlTitle' => __( 'Insert from URL', 'workmates' ),

		// Featured Images
		'setFeaturedImageTitle' => __( 'Set Featured Image', 'workmates' ),
		'setFeaturedImage'    => __( 'Set featured image', 'workmates' ),

		// Gallery
		'createGalleryTitle' => __( 'Create Gallery', 'workmates' ),
		'editGalleryTitle'   => __( 'Edit Gallery', 'workmates' ),
		'cancelGalleryTitle' => __( '&#8592; Cancel Gallery', 'workmates' ),
		'insertGallery'      => __( 'Insert gallery', 'workmates' ),
		'updateGallery'      => __( 'Update gallery', 'workmates' ),
		'addToGallery'       => __( 'Add to gallery', 'workmates' ),
		'addToGalleryTitle'  => __( 'Add to Gallery', 'workmates' ),
		'reverseOrder'       => __( 'Reverse order', 'workmates' ),
	);

	$workmates_strings = apply_filters( 'workmates_view_strings', array(
		// WorkMates
		'wmMainTitle'      => __( 'Invite Workmates', 'workmates' ),
		'wmTab'            => _x( 'WorkMates', 'WorkMates editor tab name', 'workmates' ),
		'wmInsertBtn'      => __( 'Add to invites', 'workmates' ),
		'wmNextBtn'        => __( 'Next', 'workmates' ),
		'wmPrevBtn'        => __( 'Prev', 'workmates' ),
		'wmSrcPlaceHolder' => __( 'Search', 'workmates' ),
		'invited'          => __( '%d to invite', 'workmates' ),
		'removeInviteBtn'  => __( 'Remove Invite', 'workmates' ),
	) );

	$settings = apply_filters( 'media_view_settings', $settings, $post );
	$strings  = apply_filters( 'media_view_strings',  $strings,  $post );
	$strings = array_merge( $strings, $workmates_strings );

	$strings['settings'] = $settings;

	wp_localize_script( 'workmates-media-views', '_wpMediaViewsL10n', $strings );

	wp_enqueue_script( 'workmates-modal' );
	wp_enqueue_style( 'media-views' );
	workmates_plupload_settings();

	require_once ABSPATH . WPINC . '/media-template.php';
	add_action( 'admin_footer', 'wp_print_media_templates' );
	add_action( 'wp_footer', 'wp_print_media_templates' );

	do_action( 'workmates_enqueue_editor' );
}

/**
 * Trick to make the media-views works without plupload loaded
 *
 * @package WorkMates
 * @since 1.0
 *
 * @global $wp_scripts
 */
function workmates_plupload_settings() {
	global $wp_scripts;

	$data = $wp_scripts->get_data( 'workmates-plupload', 'data' );

	if ( $data && false !== strpos( $data, '_wpPluploadSettings' ) )
		return;

	$settings = array(
		'defaults' => array(),
		'browser'  => array(
			'mobile'    => false,
			'supported' => false,
		),
		'limitExceeded' => false
	);

	$script = 'var _wpPluploadSettings = ' . json_encode( $settings ) . ';';

	if ( $data )
		$script = "$data\n$script";

	$wp_scripts->add_data( 'workmates-plupload', 'data', $script );
}


/**
 * The template needed for the WorkMates editor
 *
 * @package WorkMates
 * @since 1.0
 *
 * @global $wp_scripts
 */
function workmates_media_templates() {
	?>
	<script type="text/html" id="tmpl-workmates">
			<# if ( 1 === data.notfound  ) { #>
				<div id="workmates-error"><p><?php _e( 'No users found', 'workmates' );?></p></div>
			<# } else { #>
				<div id="user-{{ data.id }}" class="attachment-preview user type-image" data-id="{{ data.id }}">
					<div class="thumbnail">
						<div class="avatar">
							<img src="{{data.avatar}}" draggable="false" />
						</div>
						<div class="displayname">
							<strong>{{data.name}}</strong>
						</div>
					</div>
				</div>
				<a id="user-check-{{ data.id }}" class="check" href="#" title="<?php _e( 'Deselect', 'workmates' ); ?>" data-id="{{ data.id }}"><div class="media-modal-icon"></div></a>
			<# } #>
	</script>

	<script type="text/html" id="tmpl-user-selection">
		<div class="selection-info">
			<span class="count"></span>
			<# if ( data.clearable ) { #>
				<a class="clear-selection" href="#"><?php _e( 'Clear', 'workmates' ); ?></a>
			<# } #>
		</div>
		<div class="selection-view">
			<ul></ul>
		</div>
	</script>
	<?php
}

add_action( 'print_media_templates', 'workmates_media_templates' );
