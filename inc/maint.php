<?php
/**
 * TEMPORARY maintenance endpoint (build tooling — removed after use).
 *
 * Read-only diagnostics plus one narrow repair: clearing Elementor builder
 * flags on pages that were never Elementor documents, which makes Elementor
 * stop overriding the page body.
 *
 * @package ConfidentTrader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CT_MAINT_TOKEN' ) ) {
	define( 'CT_MAINT_TOKEN', 'ct-maint-4b81e2f7c05d' );
}

/**
 * Route the maintenance request.
 */
function ct_maint_router() {
	if ( empty( $_GET['ct_maint'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$token = isset( $_GET['ct_token'] ) ? (string) wp_unslash( $_GET['ct_token'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! hash_equals( CT_MAINT_TOKEN, $token ) ) {
		status_header( 403 );
		exit( 'forbidden' );
	}
	@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	$action = sanitize_key( wp_unslash( $_GET['ct_maint'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$out    = array( 'action' => $action );

	if ( 'diag' === $action ) {
		$out['theme']     = get_option( 'stylesheet' );
		$out['front']     = array( get_option( 'show_on_front' ), get_option( 'page_on_front' ), get_option( 'page_for_posts' ) );
		$out['plugins']   = (array) get_option( 'active_plugins' );
		$out['pages']     = array();
		$fragments_dir    = get_template_directory() . '/inc/content/pages/';

		foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
			$frag = $fragments_dir . $p->post_name . '.html';
			if ( ! file_exists( $frag ) && 'post' === $p->post_type ) {
				$frag = get_template_directory() . '/inc/content/posts/' . $p->post_name . '.html';
			}
			$shipped = file_exists( $frag ) ? (string) file_get_contents( $frag ) : null;
			$diff    = null;
			if ( null !== $shipped ) {
				if ( trim( $shipped ) === trim( $p->post_content ) ) {
					$diff = 'identical';
				} else {
					// first differing character
					$len = min( strlen( $shipped ), strlen( $p->post_content ) );
					$at  = 0;
					while ( $at < $len && $shipped[ $at ] === $p->post_content[ $at ] ) {
						$at++;
					}
					$diff = array(
						'first_diff_at' => $at,
						'shipped_len'   => strlen( $shipped ),
						'stored_len'    => strlen( $p->post_content ),
						'shipped_around' => substr( $shipped, max( 0, $at - 40 ), 120 ),
						'stored_around'  => substr( $p->post_content, max( 0, $at - 40 ), 120 ),
					);
				}
			}
			$out['pages'][] = array(
				'id'        => $p->ID,
				'type'      => $p->post_type,
				'slug'      => $p->post_name,
				'status'    => $p->post_status,
				'len'       => strlen( $p->post_content ),
				'managed'   => (bool) get_post_meta( $p->ID, '_ct_managed', true ),
				'el_mode'   => get_post_meta( $p->ID, '_elementor_edit_mode', true ),
				'el_data'   => strlen( (string) get_post_meta( $p->ID, '_elementor_data', true ) ),
				'el_type'   => get_post_meta( $p->ID, '_elementor_template_type', true ),
				'content'   => $diff,
			);
		}
	}

	if ( 'repair' === $action ) {
		// Only clear Elementor builder flags where Elementor has no document:
		// that is what makes Elementor replace the page body with an empty canvas.
		$out['cleared'] = array();
		foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
			$data = (string) get_post_meta( $p->ID, '_elementor_data', true );
			$mode = get_post_meta( $p->ID, '_elementor_edit_mode', true );
			if ( 'builder' !== $mode ) {
				continue;
			}
			$empty = ( '' === trim( $data ) || '[]' === trim( $data ) || '[[]]' === trim( $data ) || strlen( trim( $data ) ) < 20 );
			if ( ! $empty ) {
				$out['cleared'][] = array( 'id' => $p->ID, 'slug' => $p->post_name, 'skipped' => 'has elementor data (' . strlen( $data ) . ' bytes)' );
				continue;
			}
			foreach ( array( '_elementor_edit_mode', '_elementor_data', '_elementor_template_type', '_elementor_version', '_elementor_page_settings', '_elementor_css' ) as $key ) {
				delete_post_meta( $p->ID, $key );
			}
			$out['cleared'][] = array( 'id' => $p->ID, 'slug' => $p->post_name, 'fixed' => 'elementor builder flag cleared' );
		}
		$out['theme_after'] = get_option( 'stylesheet' );
	}

	if ( 'words' === $action ) {
		$slug = isset( $_GET['slug'] ) ? sanitize_title( wp_unslash( $_GET['slug'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$p    = get_page_by_path( $slug, OBJECT, array( 'page', 'post' ) );
		if ( ! $p ) {
			$out['error'] = 'no such page';
		} else {
			$stored = wp_strip_all_tags( strip_shortcodes( $p->post_content ) );
			$stored = trim( preg_replace( '/\s+/', ' ', $stored ) );
			$frag   = get_template_directory() . '/inc/content/pages/' . $slug . '.html';
			$ship   = file_exists( $frag ) ? trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) file_get_contents( $frag ) ) ) ) : '';
			$out['slug']        = $slug;
			$out['stored_text'] = substr( $stored, 0, 4000 );
			$out['has_40']      = preg_match_all( '/\b40\b/', $stored );
			$out['has_40_ship'] = preg_match_all( '/\b40\b/', $ship );
			$out['stored_words'] = str_word_count( $stored );
			$out['ship_words']   = str_word_count( $ship );
		}
	}

	if ( 'restore' === $action ) {
		// Re-write every managed page/post from the fragments shipped in the theme.
		// kses must be off: without unfiltered_html WordPress strips attributes such
		// as img decoding="async" and escapes entities inside comments.
		$out['restored'] = array();
		kses_remove_filters();
		remove_filter( 'content_save_pre', 'wp_filter_post_kses' );
		remove_filter( 'content_filtered_save_pre', 'wp_filter_post_kses' );

		foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
			if ( ! get_post_meta( $p->ID, '_ct_managed', true ) ) {
				continue;
			}
			$frag = get_template_directory() . '/inc/content/pages/' . $p->post_name . '.html';
			if ( ! file_exists( $frag ) ) {
				$frag = get_template_directory() . '/inc/content/posts/' . $p->post_name . '.html';
			}
			if ( ! file_exists( $frag ) ) {
				$out['restored'][] = array( 'slug' => $p->post_name, 'skipped' => 'no fragment' );
				continue;
			}
			$before = get_post_field( 'post_content', $p->ID, 'raw' );
			$after  = (string) file_get_contents( $frag );
			if ( trim( $before ) !== trim( $after ) ) {
				wp_update_post( array( 'ID' => $p->ID, 'post_content' => $after ) );
			}
			// Elementor must never take over a page the theme renders.
			foreach ( array( '_elementor_edit_mode', '_elementor_data', '_elementor_template_type', '_elementor_version', '_elementor_page_settings', '_elementor_css' ) as $key ) {
				delete_post_meta( $p->ID, $key );
			}
			$out['restored'][] = array(
				'slug'   => $p->post_name,
				'before' => strlen( (string) $before ),
				'after'  => strlen( (string) get_post_field( 'post_content', $p->ID, 'raw' ) ),
			);
		}
		kses_init_filters();
		$out['note'] = 'elementor meta cleared on all managed pages';
	}

	header( 'Content-Type: application/json; charset=utf-8' );
	echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'init', 'ct_maint_router', 98 );
