<?php
/**
 * TEMPORARY build tool: converts the managed design pages into real Elementor
 * documents (one container + one HTML widget per design section) so they can be
 * edited in the Elementor editor without touching the design markup.
 *
 * Token-gated; removed from the theme once the conversion is verified.
 *
 *   /?ct_el=status&ct_token=…
 *   /?ct_el=convert&ct_token=…
 *   /?ct_el=revert&ct_token=…
 *
 * @package ConfidentTrader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CT_EL_TOKEN' ) ) {
	define( 'CT_EL_TOKEN', 'ct-el-9d24f6a1b730' );
}

/** Elementor meta keys owned by the builder. */
function ct_el_meta_keys() {
	return array( '_elementor_edit_mode', '_elementor_data', '_elementor_template_type', '_elementor_version', '_elementor_page_settings', '_elementor_css' );
}

/**
 * Deterministic 7-char element id.
 *
 * @param string $seed Seed.
 * @return string
 */
function ct_el_id( $seed ) {
	return substr( md5( 'confident-trader|' . $seed ), 0, 7 );
}

/**
 * Split a fragment into its top-level <section> blocks.
 *
 * @param string $html Fragment.
 * @return string[]
 */
function ct_split_sections( $html ) {
	$out = array();
	$len = strlen( $html );
	$i   = 0;
	while ( false !== ( $start = strpos( $html, '<section', $i ) ) ) {
		$depth = 0;
		$p     = $start;
		$end   = false;
		while ( $p < $len ) {
			$o = strpos( $html, '<section', $p + 1 );
			$c = strpos( $html, '</section>', $p + 1 );
			if ( false === $c ) {
				break;
			}
			if ( false !== $o && $o < $c ) {
				$depth++;
				$p = $o;
				continue;
			}
			if ( 0 === $depth ) {
				$end = $c + strlen( '</section>' );
				break;
			}
			$depth--;
			$p = $c;
		}
		if ( false === $end ) {
			break;
		}
		$out[] = substr( $html, $start, $end - $start );
		$i     = $end;
	}
	return $out;
}

/**
 * Build an Elementor element tree for one fragment.
 *
 * @param string $slug      Post slug.
 * @param string $post_type Post type.
 * @return array
 */
function ct_el_build_data( $slug, $post_type = 'page' ) {
	$path = ct_fragment_path( $slug, $post_type );
	if ( ! $path ) {
		return array();
	}
	$html   = (string) file_get_contents( $path );
	// Pages are a sequence of design sections; an article body is one block.
	$chunks = ( 'post' === $post_type ) ? array( trim( $html ) ) : ct_split_sections( $html );
	if ( ! $chunks ) {
		$chunks = array( trim( $html ) );
	}

	$data = array();
	foreach ( $chunks as $n => $chunk ) {
		$data[] = array(
			'id'       => ct_el_id( $slug . '|container|' . $n ),
			'elType'   => 'container',
			'settings' => array(
				'content_width'  => 'full',
				'width'          => array( 'unit' => '%', 'size' => 100, 'sizes' => array() ),
				'flex_direction' => 'column',
				'flex_gap'       => array( 'unit' => 'px', 'column' => '0', 'row' => '0', 'isLinked' => true ),
				'gap'            => array( 'unit' => 'px', 'column' => '0', 'row' => '0', 'isLinked' => true ),
				'padding'        => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
				'margin'         => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
			),
			'elements' => array(
				array(
					'id'         => ct_el_id( $slug . '|widget|' . $n ),
					'elType'     => 'widget',
					'widgetType' => 'html',
					'settings'   => array( 'html' => $chunk ),
					'elements'   => array(),
				),
			),
			'isInner'  => false,
		);
	}
	return $data;
}

/**
 * Route the conversion tool.
 */
function ct_el_router() {
	if ( empty( $_GET['ct_el'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$token = isset( $_GET['ct_token'] ) ? (string) wp_unslash( $_GET['ct_token'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! hash_equals( CT_EL_TOKEN, $token ) ) {
		status_header( 403 );
		exit( 'forbidden' );
	}
	@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$action = sanitize_key( wp_unslash( $_GET['ct_el'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$out    = array( 'action' => $action, 'log' => array() );

	kses_remove_filters();

	if ( 'convert' === $action ) {
		foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'publish' ) ) as $p ) {
			if ( ! get_post_meta( $p->ID, '_ct_managed', true ) ) {
				continue;
			}
			$data = ct_el_build_data( $p->post_name, $p->post_type );
			if ( ! $data ) {
				$out['log'][] = $p->post_name . ': no fragment';
				continue;
			}
			// Elementor's own save path slashes the JSON because update_metadata() unslashes.
			update_post_meta( $p->ID, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
			update_post_meta( $p->ID, '_elementor_edit_mode', 'builder' );
			update_post_meta( $p->ID, '_elementor_template_type', 'post' === $p->post_type ? 'wp-post' : 'wp-page' );
			update_post_meta( $p->ID, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '4.3.4' );
			delete_post_meta( $p->ID, '_elementor_css' );

			$containers = count( $data );
			$widgets    = 0;
			foreach ( $data as $c ) {
				$widgets += count( $c['elements'] );
			}
			$out['log'][] = sprintf( '%s (%s): %d containers, %d html widgets, %d bytes', $p->post_name, $p->post_type, $containers, $widgets, strlen( (string) get_post_meta( $p->ID, '_elementor_data', true ) ) );
		}
		// Elementor caches generated CSS per document.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		$out['log'][] = 'elementor css cache cleared';
	}

	if ( 'status' === $action ) {
		$out['elementor'] = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null;
		$out['rows']      = array();
		foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'publish' ) ) as $p ) {
			if ( ! get_post_meta( $p->ID, '_ct_managed', true ) ) {
				continue;
			}
			$raw  = (string) get_post_meta( $p->ID, '_elementor_data', true );
			$data = json_decode( $raw, true );
			$c    = is_array( $data ) ? count( $data ) : 0;
			$w    = 0;
			if ( is_array( $data ) ) {
				foreach ( $data as $node ) {
					$w += isset( $node['elements'] ) ? count( $node['elements'] ) : 0;
				}
			}
			$out['rows'][] = array(
				'slug'       => $p->post_name,
				'type'       => $p->post_type,
				'id'         => $p->ID,
				'mode'       => get_post_meta( $p->ID, '_elementor_edit_mode', true ),
				'data_bytes' => strlen( $raw ),
				'json_ok'    => is_array( $data ),
				'containers' => $c,
				'widgets'    => $w,
				'editable'   => ( 'builder' === get_post_meta( $p->ID, '_elementor_edit_mode', true ) && is_array( $data ) && $c > 0 ),
			);
		}
	}

	if ( 'revert' === $action ) {
		foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'publish' ) ) as $p ) {
			if ( ! get_post_meta( $p->ID, '_ct_managed', true ) ) {
				continue;
			}
			foreach ( ct_el_meta_keys() as $key ) {
				delete_post_meta( $p->ID, $key );
			}
			$out['log'][] = $p->post_name . ': elementor meta removed';
		}
	}

	if ( 'blank' === $action ) {
		// Simulate "the client deleted every section in Elementor" on one page:
		// the page must fall back to the design, never publish a blank canvas.
		$slug = isset( $_GET['slug'] ) ? sanitize_title( wp_unslash( $_GET['slug'] ) ) : 'home'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$p    = get_page_by_path( $slug, OBJECT, array( 'page', 'post' ) );
		if ( ! $p ) {
			$out['error'] = 'no such page';
		} else {
			update_post_meta( $p->ID, '_elementor_edit_mode', 'builder' );
			update_post_meta( $p->ID, '_elementor_data', '[]' );
			$out['blanked'] = $slug . ' (id ' . $p->ID . ')';
			$out['expect']  = 'the live page must still render the original design';
		}
	}

	if ( 'clean' === $action ) {
		// Remove the submissions created while testing the forms.
		$n = 0;
		foreach ( get_posts( array( 'post_type' => 'ct_submission', 'numberposts' => -1, 'post_status' => 'any' ) ) as $s ) {
			if ( false !== strpos( $s->post_title, 'QA Bot' ) ) {
				wp_delete_post( $s->ID, true );
				$n++;
			}
		}
		$out['deleted'] = $n;
		$out['remaining'] = count( get_posts( array( 'post_type' => 'ct_submission', 'numberposts' => -1, 'post_status' => 'any' ) ) );
	}

	kses_init_filters();
	header( 'Content-Type: application/json; charset=utf-8' );
	echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	exit;
}
add_action( 'init', 'ct_el_router', 97 );
