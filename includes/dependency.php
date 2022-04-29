<?php
/**
 * WorkMates dependency functions.
 *
 * @package WorkMates
 * @since  2.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'bp_nouveau_group_print_invites_placeholders' ) ) {
	function bp_nouveau_group_print_invites_placeholders() {
		if ( bp_is_group_create() ) : ?>

			<h3 class="bp-screen-title creation-step-name">
				<?php esc_html_e( 'Invite Members', 'buddypress' ); ?>
			</h3>

		<?php else : ?>

			<h2 class="bp-screen-title">
				<?php esc_html_e( 'Invite Members', 'buddypress' ); ?>
			</h2>

		<?php endif; ?>

		<div id="group-invites-container">
			<nav class="<?php bp_nouveau_single_item_subnav_classes(); ?>" id="subnav" role="navigation" aria-label="<?php esc_attr_e( 'Group invitations menu', 'buddypress' ); ?>"></nav>
			<div class="group-invites-column">
				<div class="subnav-filters group-subnav-filters bp-invites-filters"></div>
				<div class="bp-invites-feedback"></div>
				<div class="members bp-invites-content"></div>
			</div>
		</div>
		<?php
	}
}

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
