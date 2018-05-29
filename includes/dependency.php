<?php
/**
 * WorkMates dependency functions.
 *
 * @package WorkMates
 * @since  2.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
