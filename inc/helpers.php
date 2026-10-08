<?php
/**
 * Small view helpers shared by the templates.
 *
 * @package ConfidentTrader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG brand mark (the Stitch export referenced a raster logo we do not own).
 *
 * @param string $gradient_id Unique gradient id.
 * @param string $classes     Utility classes for the svg element.
 * @return string
 */
function ct_logo_svg( $gradient_id = 'ctlg', $classes = 'h-8 w-8' ) {
	$id = esc_attr( $gradient_id );
	return sprintf(
		'<svg class="%1$s shrink-0" viewBox="0 0 32 32" role="img" aria-label="%2$s" focusable="false">'
		. '<defs><linearGradient id="%3$s" x1="0" y1="1" x2="1" y2="0">'
		. '<stop offset="0%%" stop-color="#00f2fe"/><stop offset="100%%" stop-color="#41eec1"/></linearGradient></defs>'
		. '<rect x="1" y="1" width="30" height="30" rx="7" fill="#0b0e15" stroke="url(#%3$s)" stroke-width="1.5"/>'
		. '<path d="M7 22.5 12 17.5 16.5 20 25 10" fill="none" stroke="url(#%3$s)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>'
		. '<circle cx="25" cy="10" r="2.6" fill="#41eec1"/></svg>',
		esc_attr( $classes ),
		esc_attr__( 'Confident Trader', 'confident-trader' ),
		$id
	);
}

/**
 * True when the current request matches a design route.
 *
 * @param string $path Root-relative path with trailing slash.
 * @return bool
 */
function ct_is_current( $path ) {
	$current = trailingslashit( wp_parse_url( ct_current_url(), PHP_URL_PATH ) );
	if ( '/' === $current || '' === $current ) {
		return '/' === $path;
	}
	if ( $current === $path ) {
		return true;
	}
	// Treat a singular page as current for its own archive parent.
	return false !== strpos( $path, $current ) && '/' !== $current;
}

/**
 * Current request path.
 *
 * @return string
 */
function ct_current_url() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	return strtok( $uri, '?' );
}

/**
 * Badge / pill used across the design system.
 *
 * @param string $text  Label.
 * @param string $tone  primary|secondary|amber|error.
 * @param string $extra Extra utility classes.
 * @return string
 */
function ct_pill( $text, $tone = 'secondary', $extra = '' ) {
	$map = array(
		'primary'   => 'bg-primary-container/10 text-primary-container',
		'secondary' => 'bg-secondary/10 text-secondary',
		'amber'     => 'bg-tertiary-fixed-dim/10 text-tertiary-fixed-dim',
		'error'     => 'bg-error-container/20 text-error',
	);
	$tone = isset( $map[ $tone ] ) ? $tone : 'secondary';
	return sprintf(
		'<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full %1$s font-label-caps text-label-caps uppercase font-bold %2$s">%3$s</span>',
		$map[ $tone ],
		esc_attr( $extra ),
		esc_html( $text )
	);
}

/**
 * Material-symbols icon span.
 *
 * @param string $name  Icon name (subset font).
 * @param string $class Extra classes.
 * @param int    $fill  FILL axis 0|1.
 * @return string
 */
function ct_icon( $name, $class = '', $fill = 0 ) {
	$style = $fill ? ' style="font-variation-settings: \'FILL\' 1;"' : '';
	return sprintf(
		'<span class="material-symbols-outlined %1$s" aria-hidden="true"%2$s>%3$s</span>',
		esc_attr( $class ),
		$style,
		esc_html( $name )
	);
}

/**
 * Human "x min read" label for the current post.
 *
 * @return string
 */
function ct_read_time() {
	$post = get_post();
	if ( ! $post ) {
		return '';
	}
	$words = str_word_count( wp_strip_all_tags( $post->post_content ) );
	$mins  = max( 1, (int) ceil( $words / 220 ) );
	/* translators: %d: minutes. */
	return sprintf( _n( '%d min read', '%d min read', $mins, 'confident-trader' ), $mins );
}

/**
 * Reusable section eyebrow + heading + intro block.
 *
 * @param string $eyebrow Small uppercase label.
 * @param string $title   Heading (already escaped HTML allowed).
 * @param string $intro   Supporting copy.
 * @param string $tag     Heading tag.
 * @return string
 */
function ct_section_head( $eyebrow, $title, $intro = '', $tag = 'h2' ) {
	$tag = in_array( $tag, array( 'h1', 'h2', 'h3' ), true ) ? $tag : 'h2';
	$out = '<div class="text-center max-w-3xl mx-auto mb-space-xl">';
	if ( $eyebrow ) {
		$out .= '<span class="font-label-caps text-label-caps text-primary uppercase tracking-widest">' . esc_html( $eyebrow ) . '</span>';
	}
	$out .= sprintf(
		'<%1$s class="font-headline-xl text-headline-xl text-on-surface tracking-tight mt-1 mb-space-sm">%2$s</%1$s>',
		$tag,
		$title
	);
	if ( $intro ) {
		$out .= '<p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">' . $intro . '</p>';
	}
	$out .= '</div>';
	return $out;
}
