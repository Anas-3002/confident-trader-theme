<?php
/**
 * Confident Trader theme bootstrap.
 *
 * @package ConfidentTrader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CT_VERSION', '1.0.0' );
define( 'CT_CONTENT_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/forms.php';
require_once get_template_directory() . '/inc/managed-pages.php';
require_once get_template_directory() . '/inc/elementor-convert.php'; // TEMP build tool, removed after conversion.
// inc/installer.php (one-time provisioning) is intentionally not shipped: the
// build it performed is complete and the trigger should not exist in production.
// Restore it from git history (commit 077d8cd) if the environment is ever rebuilt.
// inc/maint.php was a temporary diagnostic/repair endpoint and has been removed.

/**
 * Render the form markers that live inside stored page content.
 *
 * Nonces and admin-post actions cannot be stored, so the fragment keeps a
 * marker and the form is rendered per request.
 *
 * @param string $content Post content.
 * @return string
 */
function ct_render_form_markers( $content ) {
	if ( false === strpos( $content, '<!--CT_FORM:' ) ) {
		return $content;
	}
	return preg_replace_callback(
		'/<!--CT_FORM:([a-z_]+)-->/',
		function ( $m ) {
			return ct_form( $m[1] );
		},
		$content
	);
}
add_filter( 'the_content', 'ct_render_form_markers', 20 );

/**
 * True while Elementor is rendering its editor or preview.
 *
 * The design pages are Elementor documents now, so in the editor we let the
 * builder load its own assets (that is what the editor expects); on the public
 * site its framework is dropped because the design needs none of it.
 *
 * @return bool
 */
function ct_is_elementor_editor_request() {
	if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}
	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		return true;
	}
	return false;
}

/**
 * Trim WordPress/plugin front-end CSS that this theme does not use.
 *
 * The design pages are raw design markup (no core blocks), so the block library,
 * classic theme styles and the merged global stylesheet are dead weight: measured
 * at 989 + 94 + 76 rules before this filter.
 */
function ct_trim_frontend_css() {
	if ( is_admin() ) {
		return;
	}
	foreach ( array(
		'wp-block-library',
		'wp-block-library-theme',
		'classic-theme-styles',
		'global-styles',
		'wp-img-auto-sizes-contain',
		'core-block-supports',
	) as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}

	// Third-party plugin sheets that ship a global stylesheet on every page.
	global $wp_styles;
	if ( ! $wp_styles instanceof WP_Styles ) {
		return;
	}
	$keep_builder = ct_is_elementor_editor_request();
	foreach ( (array) $wp_styles->registered as $handle => $style ) {
		$src = isset( $style->src ) ? (string) $style->src : '';
		if ( '' === $src ) {
			continue;
		}
		if ( $keep_builder && preg_match( '#(elementor-frontend|elementor-icons)#', $src ) ) {
			continue;
		}
		if ( preg_match( '#(hostinger-reach|/blocks/subscription|elementor-frontend|elementor-icons)#', $src ) ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'ct_trim_frontend_css', 100 );

/**
 * Drop Elementor's front-end framework from the public site.
 *
 * Every design page is an Elementor document built from HTML widgets, so none of
 * Elementor's own CSS or JS is needed to render them — and all of it would fight
 * the design's compiled CSS. It is kept for the editor and preview, where the
 * builder expects its own assets.
 */
function ct_dequeue_elementor_on_theme_pages() {
	if ( is_admin() && ! isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ct_is_elementor_editor_request() ) {
		return;
	}
	global $wp_styles, $wp_scripts;
	foreach ( array( $wp_styles, $wp_scripts ) as $collection ) {
		if ( ! $collection || empty( $collection->queue ) ) {
			continue;
		}
		foreach ( (array) $collection->queue as $handle ) {
			if ( 0 === strpos( (string) $handle, 'elementor' ) ) {
				wp_dequeue_style( $handle );
				wp_dequeue_script( $handle );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'ct_dequeue_elementor_on_theme_pages', 99 );

/**
 * Second, late pass over the builder's assets — on the public site only.
 *
 * Elementor registers some of its stylesheets (its generated base CSS, and the
 * Google Fonts the default kit asks for) after wp_enqueue_scripts has run, so a
 * prefix dequeue there misses them. Nothing here is used by the design, and the
 * fonts are self-hosted, so dropping them also removes several external requests.
 */
function ct_strip_builder_assets_late() {
	if ( is_admin() && ! isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ct_is_elementor_editor_request() ) {
		return;
	}
	global $wp_styles;
	if ( ! $wp_styles instanceof WP_Styles || empty( $wp_styles->queue ) ) {
		return;
	}
	foreach ( (array) $wp_styles->queue as $handle ) {
		$handle = (string) $handle;
		$src    = isset( $wp_styles->registered[ $handle ]->src ) ? (string) $wp_styles->registered[ $handle ]->src : '';
		$drop   = 0 === strpos( $handle, 'elementor' )
			|| in_array( $handle, array( 'base-desktop', 'base-mobile', 'base-desktop-css', 'base-mobile-css' ), true )
			|| in_array( $handle, array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wp-img-auto-sizes-contain', 'core-block-supports' ), true )
			|| false !== strpos( $src, 'fonts.googleapis.com' )
			|| preg_match( '#(hostinger-reach|/blocks/subscription)#', $src );
		if ( $drop ) {
			wp_dequeue_style( $handle );
		}
	}
}
add_action( 'wp_print_styles', 'ct_strip_builder_assets_late', 1 );

/**
 * Final backstop: drop those stylesheets at the point the <link> is printed.
 *
 * Some plugins enqueue their block stylesheet during wp_head, after every
 * dequeue hook has run, so the queue is no longer the place to intercept them.
 *
 * @param string $tag    Link tag markup.
 * @param string $handle Style handle.
 * @param string $href   Style URL.
 * @return string
 */
function ct_strip_stylesheet_tag( $tag, $handle, $href = '' ) {
	if ( is_admin() || ct_is_elementor_editor_request() ) {
		return $tag;
	}
	$handle = (string) $handle;
	$href   = (string) $href;
	$drop   = 0 === strpos( $handle, 'elementor' )
		|| in_array( $handle, array( 'base-desktop', 'base-mobile', 'base-desktop-css', 'base-mobile-css', 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wp-img-auto-sizes-contain', 'core-block-supports' ), true )
		|| preg_match( '#(fonts\.googleapis\.com|hostinger-reach|/blocks/subscription|elementor-frontend|elementor-icons|base-(desktop|mobile)\.css)#', $href );
	return $drop ? '' : $tag;
}
add_filter( 'style_loader_tag', 'ct_strip_stylesheet_tag', 10, 3 );

/**
 * Theme supports.
 */
function ct_setup() {
	load_theme_textdomain( 'confident-trader', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 40, 'width' => 40, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'confident-trader' ),
			'footer'  => __( 'Footer Navigation', 'confident-trader' ),
		)
	);
}
add_action( 'after_setup_theme', 'ct_setup' );

/**
 * Content width.
 */
function ct_content_width() {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'ct_content_width', 0 );

/**
 * Front-end assets.
 */
function ct_assets() {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	wp_enqueue_style( 'ct-fonts', $uri . '/assets/css/fonts.css', array(), ct_asset_version( '/assets/css/fonts.css' ) );
	wp_enqueue_style( 'ct-theme', $uri . '/assets/css/theme.css', array( 'ct-fonts' ), ct_asset_version( '/assets/css/theme.css' ) );
	wp_enqueue_style( 'ct-design', $uri . '/assets/css/design.css', array( 'ct-theme' ), ct_asset_version( '/assets/css/design.css' ) );
	wp_enqueue_style( 'ct-style', get_stylesheet_uri(), array( 'ct-design' ), ct_asset_version( '/style.css' ) );

	wp_enqueue_script( 'ct-theme', $uri . '/assets/js/theme.js', array(), ct_asset_version( '/assets/js/theme.js' ), true );
	wp_script_add_data( 'ct-theme', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'ct_assets' );

/**
 * Cache-busting version from file mtime.
 *
 * @param string $rel Relative path inside the theme.
 * @return string
 */
function ct_asset_version( $rel ) {
	$path = get_template_directory() . $rel;
	return file_exists( $path ) ? (string) filemtime( $path ) : CT_VERSION;
}

/**
 * Body classes so the design's base surface, font and selection colours apply.
 *
 * @param array $classes Body classes.
 * @return array
 */
function ct_body_class( $classes ) {
	$classes[] = 'ct-site';
	if ( is_front_page() ) {
		$classes[] = 'ct-home';
	}
	return $classes;
}
add_filter( 'body_class', 'ct_body_class' );

/**
 * Meta viewport + charset are core; keep the design's shell attributes.
 */
function ct_language_attributes( $output ) {
	return $output . ' class="dark"';
}
add_filter( 'language_attributes', 'ct_language_attributes' );

/**
 * Never let wpautop reformat the managed design markup.
 */
function ct_remove_autop() {
	if ( is_singular() ) {
		$post = get_post();
		if ( $post && get_post_meta( $post->ID, '_ct_managed', true ) ) {
			remove_filter( 'the_content', 'wpautop', 10 );
			remove_filter( 'the_content', 'wptexturize', 10 );
			remove_filter( 'the_content', 'convert_smilies', 20 );
		}
	}
}
add_action( 'wp', 'ct_remove_autop' );

/**
 * Excerpt length for insight cards.
 *
 * @return int
 */
function ct_excerpt_length() {
	return 26;
}
add_filter( 'excerpt_length', 'ct_excerpt_length' );

/**
 * Excerpt "read more" suffix.
 *
 * @return string
 */
function ct_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'ct_excerpt_more' );

/**
 * Register the insight post type category archive nicety: 12 posts per page.
 *
 * @param WP_Query $query Query.
 */
function ct_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_search() ) {
		$query->set( 'posts_per_page', 9 );
	}
}
add_action( 'pre_get_posts', 'ct_pre_get_posts' );

/**
 * Drop the emoji/shortlink head cruft — small performance win.
 */
function ct_clean_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'init', 'ct_clean_head' );
