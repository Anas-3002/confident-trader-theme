<?php
/**
 * One-time provisioning routine for the Confident Trader build.
 *
 * Never runs automatically: every stage requires the build token in the query
 * string. Once provisioning is complete, remove the token (or the file).
 *
 *   /?ct_build=status&ct_token=TOKEN
 *   /?ct_build=plugins&ct_token=TOKEN
 *   /?ct_build=content&ct_token=TOKEN
 *   /?ct_build=options&ct_token=TOKEN
 *   /?ct_build=all&ct_token=TOKEN
 *
 * @package ConfidentTrader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Build-only shared secret. Rotate or blank this to disable the installer. */
if ( ! defined( 'CT_BUILD_TOKEN' ) ) {
	define( 'CT_BUILD_TOKEN', 'ct-7f3a91c25de84b06' );
}

/**
 * Bootstrap the installer on init.
 */
function ct_build_router() {
	if ( empty( $_GET['ct_build'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$token = isset( $_GET['ct_token'] ) ? (string) wp_unslash( $_GET['ct_token'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! CT_BUILD_TOKEN || ! hash_equals( CT_BUILD_TOKEN, $token ) ) {
		status_header( 403 );
		exit( 'forbidden' );
	}

	@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$stage  = sanitize_key( wp_unslash( $_GET['ct_build'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$result = array( 'stage' => $stage, 'log' => array() );

	try {
		switch ( $stage ) {
			case 'status':
				$result['log'] = ct_build_status();
				break;
			case 'plugins':
				ct_build_plugins( $result );
				break;
			case 'content':
				ct_build_content( $result );
				break;
			case 'options':
				ct_build_options( $result );
				break;
			case 'all':
				ct_build_plugins( $result );
				ct_build_content( $result );
				ct_build_options( $result );
				break;
			default:
				$result['log'][] = 'unknown stage';
		}
	} catch ( Throwable $e ) {
		$result['log'][] = 'EXCEPTION: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine();
	}

	header( 'Content-Type: application/json; charset=utf-8' );
	echo wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'init', 'ct_build_router', 99 );

/**
 * Environment report.
 *
 * @return array
 */
function ct_build_status() {
	$theme = wp_get_theme();
	return array(
		'site'              => get_bloginfo( 'url' ),
		'wp'                => get_bloginfo( 'version' ),
		'php'               => PHP_VERSION,
		'theme'             => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ),
		'content_version'   => get_option( 'ct_content_version' ),
		'permalinks'        => get_option( 'permalink_structure' ),
		'show_on_front'     => get_option( 'show_on_front' ),
		'page_on_front'     => get_option( 'page_on_front' ),
		'page_for_posts'    => get_option( 'page_for_posts' ),
		'active_plugins'    => (array) get_option( 'active_plugins' ),
		'elementor_version' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
		'writable_plugins'  => wp_is_writable( WP_PLUGIN_DIR ),
		'page_count'        => (int) wp_count_posts( 'page' )->publish,
		'post_count'        => (int) wp_count_posts( 'post' )->publish,
		'managed_pages'     => count( get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'meta_key' => '_ct_managed', 'fields' => 'ids' ) ) ),
	);
}

/**
 * Read the provisioning manifest.
 *
 * @return array
 */
function ct_manifest() {
	$path = get_template_directory() . '/inc/content/site.json';
	$json = file_exists( $path ) ? file_get_contents( $path ) : '';
	$data = json_decode( (string) $json, true );
	return is_array( $data ) ? $data : array();
}

/**
 * Read one content fragment.
 *
 * @param string $kind pages|posts.
 * @param string $slug Slug.
 * @return string
 */
function ct_fragment( $kind, $slug ) {
	$path = get_template_directory() . '/inc/content/' . $kind . '/' . $slug . '.html';
	return file_exists( $path ) ? (string) file_get_contents( $path ) : '';
}

/**
 * Install and activate the required plugins from wordpress.org.
 *
 * @param array $result Result accumulator.
 */
function ct_build_plugins( &$result ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

	$manifest = ct_manifest();
	$wanted   = isset( $manifest['plugins'] ) ? (array) $manifest['plugins'] : array();

	foreach ( $wanted as $slug ) {
		$slug = sanitize_key( $slug );
		$file = ct_plugin_file( $slug );
		if ( $file && is_plugin_active( $file ) ) {
			$result['log'][] = "$slug: already active ($file)";
			continue;
		}
		if ( ! $file ) {
			$api = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );
			if ( is_wp_error( $api ) ) {
				$result['log'][] = "$slug: API error " . $api->get_error_message();
				continue;
			}
			$skin     = new Automatic_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$done     = $upgrader->install( $api->download_link );
			$result['log'][] = "$slug: install " . ( is_wp_error( $done ) ? $done->get_error_message() : ( $done ? 'ok' : 'failed' ) );
			$file = ct_plugin_file( $slug );
		}
		if ( $file ) {
			$act = activate_plugin( $file );
			$result['log'][] = "$slug: activate " . ( is_wp_error( $act ) ? $act->get_error_message() : 'ok' );
		} else {
			$result['log'][] = "$slug: plugin directory not found after install";
		}
	}
}

/**
 * Resolve a plugin slug to its main file.
 *
 * @param string $slug Plugin slug.
 * @return string
 */
function ct_plugin_file( $slug ) {
	$dir = WP_PLUGIN_DIR . '/' . $slug;
	if ( ! is_dir( $dir ) ) {
		return '';
	}
	$files = glob( $dir . '/*.php' );
	$main  = $dir . '/' . $slug . '.php';
	if ( file_exists( $main ) ) {
		return $slug . '/' . $slug . '.php';
	}
	foreach ( (array) $files as $f ) {
		$head = (string) file_get_contents( $f, false, null, 0, 8192 );
		if ( false !== stripos( $head, 'Plugin Name:' ) ) {
			return $slug . '/' . basename( $f );
		}
	}
	return '';
}

/**
 * Create or update every page and post from the manifest.
 *
 * @param array $result Result accumulator.
 */
function ct_build_content( &$result ) {
	$manifest = ct_manifest();
	if ( ! $manifest ) {
		$result['log'][] = 'manifest missing or invalid';
		return;
	}

	$ids = array();

	foreach ( $manifest['pages'] as $page ) {
		$slug    = $page['slug'];
		$content = ct_fragment( 'pages', $slug );
		if ( ! $content ) {
			$result['log'][] = "page $slug: fragment missing";
			continue;
		}
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		$payload  = array(
			'post_title'   => html_entity_decode( $page['title'], ENT_QUOTES, 'UTF-8' ),
			'post_name'    => $slug,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);
		if ( $existing ) {
			$payload['ID'] = $existing->ID;
			$id            = wp_update_post( $payload, true );
		} else {
			$id = wp_insert_post( $payload, true );
		}
		if ( is_wp_error( $id ) ) {
			$result['log'][] = "page $slug: " . $id->get_error_message();
			continue;
		}
		update_post_meta( $id, '_ct_managed', 1 );
		update_post_meta( $id, '_ct_meta_description', $page['meta_description'] );
		if ( ! empty( $page['faq'] ) ) {
			update_post_meta( $id, '_ct_faq', wp_json_encode( $page['faq'], JSON_UNESCAPED_UNICODE ) );
		}
		$ids[ $slug ] = $id;
		$result['log'][] = "page $slug: " . ( $existing ? 'updated' : 'created' ) . " ($id)";
	}

	// Blog index page.
	$blog = get_page_by_path( 'blog', OBJECT, 'page' );
	$blog_payload = array(
		'post_title'   => 'Insights',
		'post_name'    => 'blog',
		'post_content' => '<p>Research notes, methodology write-ups and field observations from the execution floor. Start with the desk programmes or browse the archive below.</p>',
		'post_status'  => 'publish',
		'post_type'    => 'page',
	);
	if ( $blog ) {
		$blog_payload['ID'] = $blog->ID;
		$blog_id            = wp_update_post( $blog_payload, true );
	} else {
		$blog_id = wp_insert_post( $blog_payload, true );
	}
	if ( ! is_wp_error( $blog_id ) ) {
		update_post_meta( $blog_id, '_ct_meta_description', 'Order-flow research, auction market theory, risk engineering and prop firm methodology from the Confident Trader live desk.' );
		$ids['blog']     = $blog_id;
		$result['log'][] = 'page blog: ' . ( $blog ? 'updated' : 'created' ) . " ($blog_id)";
	}

	// Categories.
	$cat_ids = array();
	foreach ( (array) $manifest['categories'] as $name ) {
		$term = term_exists( $name, 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'category' );
		}
		if ( ! is_wp_error( $term ) ) {
			$cat_ids[ $name ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}
	}
	$result['log'][] = 'categories: ' . count( $cat_ids ) . ' ready';

	// Posts.
	foreach ( $manifest['posts'] as $post ) {
		$slug    = $post['slug'];
		$content = ct_fragment( 'posts', $slug );
		if ( ! $content ) {
			$result['log'][] = "post $slug: fragment missing";
			continue;
		}
		$existing = get_page_by_path( $slug, OBJECT, 'post' );
		$payload  = array(
			'post_title'   => $post['title'],
			'post_name'    => $slug,
			'post_content' => $content,
			'post_excerpt' => $post['excerpt'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
		);
		if ( $existing ) {
			$payload['ID'] = $existing->ID;
			$id            = wp_update_post( $payload, true );
		} else {
			$id = wp_insert_post( $payload, true );
		}
		if ( is_wp_error( $id ) ) {
			$result['log'][] = "post $slug: " . $id->get_error_message();
			continue;
		}
		update_post_meta( $id, '_ct_managed', 1 );
		if ( isset( $cat_ids[ $post['category'] ] ) ) {
			wp_set_post_terms( $id, array( $cat_ids[ $post['category'] ] ), 'category', false );
		}
		if ( ! empty( $post['tags'] ) ) {
			wp_set_post_terms( $id, $post['tags'], 'post_tag', false );
		}
		$result['log'][] = "post $slug: " . ( $existing ? 'updated' : 'created' ) . " ($id)";
	}

	update_option( 'ct_content_version', $manifest['content_version'] );
	$result['log'][] = 'content version set to ' . $manifest['content_version'];
}

/**
 * Site options, front page routing, menus and cleanup.
 *
 * @param array $result Result accumulator.
 */
function ct_build_options( &$result ) {
	$manifest = ct_manifest();
	$site     = isset( $manifest['site'] ) ? $manifest['site'] : array();
	$options  = isset( $manifest['options'] ) ? $manifest['options'] : array();

	update_option( 'blogname', $site['name'] );
	update_option( 'blogdescription', $site['tagline'] );
	update_option( 'admin_email', $site['admin_email'] );
	update_option( 'timezone_string', $site['timezone'] );
	update_option( 'permalink_structure', $options['permalink_structure'] );
	update_option( 'blog_public', (int) $options['blog_public'] );
	update_option( 'posts_per_page', (int) $options['posts_per_page'] );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'blog_public', 1 );

	$front = get_page_by_path( $options['front_page'], OBJECT, 'page' );
	$posts = get_page_by_path( $options['posts_page'], OBJECT, 'page' );
	update_option( 'show_on_front', 'page' );
	if ( $front ) {
		update_option( 'page_on_front', $front->ID );
	}
	if ( $posts ) {
		update_option( 'page_for_posts', $posts->ID );
	}
	$result['log'][] = 'front page -> ' . ( $front ? $front->ID : 'missing' ) . ', posts page -> ' . ( $posts ? $posts->ID : 'missing' );

	// Privacy policy pointer (never publish legal text we did not write).
	$privacy = get_page_by_path( 'privacy-policy', OBJECT, 'page' );
	if ( $privacy ) {
		update_option( 'wp_page_for_privacy_policy', $privacy->ID );
	}

	// Remove WordPress sample content.
	foreach ( array( array( 'sample-page', 'page' ), array( 'hello-world', 'post' ), array( 'privacy-policy', 'page' ) ) as $row ) {
		$obj = get_page_by_path( $row[0], OBJECT, $row[1] );
		if ( $obj ) {
			$keep = get_post_meta( $obj->ID, '_ct_managed', true );
			if ( $keep ) {
				continue;
			}
			wp_delete_post( $obj->ID, true );
			$result['log'][] = "removed default {$row[0]}";
		}
	}

	// Menus.
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( (array) $manifest['menus'] as $location => $items ) {
		$menu_name = ( 'primary' === $location ) ? 'Primary Navigation' : 'Footer Navigation';
		$menu      = wp_get_nav_menu_object( $menu_name );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $menu_name );
			if ( is_wp_error( $menu_id ) ) {
				$result['log'][] = "menu $menu_name: " . $menu_id->get_error_message();
				continue;
			}
		} else {
			$menu_id = (int) $menu->term_id;
			foreach ( wp_get_nav_menu_items( $menu_id ) as $item ) {
				wp_delete_post( $item->ID, true );
			}
		}
		$order = 1;
		foreach ( $items as $item ) {
			list( $label, $url ) = $item;
			$slug    = trim( $url, '/' );
			$page    = $slug ? get_page_by_path( $slug, OBJECT, 'page' ) : null;
			if ( 'blog' === $slug ) {
				$page = get_page_by_path( 'blog', OBJECT, 'page' );
			}
			$args = array(
				'menu-item-title'  => html_entity_decode( $label, ENT_QUOTES, 'UTF-8' ),
				'menu-item-status' => 'publish',
				'menu-item-position' => $order++,
			);
			if ( $page ) {
				$args['menu-item-object-id'] = $page->ID;
				$args['menu-item-object']    = 'page';
				$args['menu-item-type']      = 'post_type';
			} else {
				$args['menu-item-url']  = home_url( $url );
				$args['menu-item-type'] = 'custom';
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
		}
		$locations[ $location ] = $menu_id;
		$result['log'][]        = "menu $menu_name: " . count( $items ) . ' items';
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	// Elementor: keep its front-end payload off pages the theme renders.
	update_option( 'elementor_disable_color_schemes', 'yes' );
	update_option( 'elementor_disable_typography_schemes', 'yes' );
	update_option( 'elementor_cpt_support', array( 'page', 'post' ) );
	update_option( 'elementor_experiment-e_atomic_elements', 'active' );

	flush_rewrite_rules( true );
	$result['log'][] = 'rewrite rules flushed';
}
