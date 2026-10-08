<?php
/**
 * Managed design pages: rendering and editor protection.
 *
 * The Stitch design pages are authored markup, version-controlled in the theme
 * under inc/content/. WordPress editors and page builders re-serialize and
 * sanitise whatever they save, which strips the design's classes and injects
 * <p> tags — that silently destroys the layout.
 *
 * These pages therefore render from the shipped fragment rather than from the
 * database, so no editor, plugin or page builder can change the live output.
 * The stored post_content is kept in sync for search and admin previews.
 *
 * @package ConfidentTrader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path to the fragment that backs a managed post.
 *
 * @param string $slug      Post slug.
 * @param string $post_type Post type.
 * @return string Empty when there is no fragment.
 */
function ct_fragment_path( $slug, $post_type = 'page' ) {
	$dir  = 'post' === $post_type ? 'posts' : 'pages';
	$path = get_template_directory() . '/inc/content/' . $dir . '/' . sanitize_file_name( $slug ) . '.html';
	return file_exists( $path ) ? $path : '';
}

/**
 * Render managed content from the version-controlled fragment.
 *
 * Runs late so it also overrides a page builder's own the_content filter.
 *
 * @param string $content Post content.
 * @return string
 */
function ct_render_managed_fragment( $content ) {
	$post = get_post();
	if ( ! $post || ! get_post_meta( $post->ID, '_ct_managed', true ) ) {
		return $content;
	}
	$path = ct_fragment_path( $post->post_name, $post->post_type );
	if ( ! $path ) {
		return $content;
	}
	return (string) file_get_contents( $path );
}
add_filter( 'the_content', 'ct_render_managed_fragment', 99 );

/**
 * Never let Elementor adopt a managed page.
 *
 * Elementor marks a post as its own with `_elementor_edit_mode`; on a managed
 * page that flag replaces the body with an (empty) builder canvas.
 *
 * @param int     $post_id Post id.
 * @param WP_Post $post    Post object.
 */
function ct_strip_elementor_on_managed( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || ! $post instanceof WP_Post ) {
		return;
	}
	if ( ! get_post_meta( $post_id, '_ct_managed', true ) ) {
		return;
	}
	foreach ( array( '_elementor_edit_mode', '_elementor_data', '_elementor_template_type', '_elementor_version', '_elementor_page_settings', '_elementor_css' ) as $key ) {
		delete_post_meta( $post_id, $key );
	}
}
add_action( 'save_post', 'ct_strip_elementor_on_managed', 10, 2 );

/**
 * Keep the block editor away from managed pages.
 *
 * The block editor re-serialises content and applies kses/autop, which is what
 * mangles the design markup.
 *
 * @param bool    $use       Whether to use the block editor.
 * @param WP_Post $post_type Post object.
 * @return bool
 */
function ct_no_block_editor_for_managed( $use, $post = null ) {
	if ( $post instanceof WP_Post && get_post_meta( $post->ID, '_ct_managed', true ) ) {
		return false;
	}
	return $use;
}
add_filter( 'use_block_editor_for_post', 'ct_no_block_editor_for_managed', 10, 2 );

/**
 * Tell the administrator, on the edit screen, where the copy actually lives.
 */
function ct_managed_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->base, array( 'post' ), true ) ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! get_post_meta( $post_id, '_ct_managed', true ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p><strong>%s</strong> %s</p><p>%s</p></div>',
		esc_html__( 'This page is a design template.', 'confident-trader' ),
		esc_html__( 'Its markup lives in the theme (inc/content/) and is rendered from there, so editing the text below will not change the live site — and saving here cannot break the layout either.', 'confident-trader' ),
		esc_html__( 'To change wording on this page, ask your developer: it is a one-line change in the theme, and it survives every future update.', 'confident-trader' )
	);
}
add_action( 'admin_notices', 'ct_managed_admin_notice' );
