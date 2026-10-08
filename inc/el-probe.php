<?php
/**
 * TEMPORARY build tool: introspects this Elementor install and provisions the
 * credentials needed to drive its official MCP server. Removed once the native
 * element conversion is done.
 *
 *   /?ct_el=types&ct_token=…
 *   /?ct_el=creds&ct_token=…      (creates one application password, returns it once)
 *   /?ct_el=revoke&ct_token=…     (revokes every application password named here)
 *
 * @package ConfidentTrader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CT_ELP_TOKEN' ) ) {
	define( 'CT_ELP_TOKEN', 'ct-elp-3f81c47ad902' );
}

if ( ! defined( 'CT_ELP_APP' ) ) {
	define( 'CT_ELP_APP', 'hermes-el-build' );
}

/**
 * Router.
 */
function ct_elp_router() {
	if ( empty( $_GET['ct_el'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$token = isset( $_GET['ct_token'] ) ? (string) wp_unslash( $_GET['ct_token'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! hash_equals( CT_ELP_TOKEN, $token ) ) {
		status_header( 403 );
		exit( 'forbidden' );
	}
	@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$action = sanitize_key( wp_unslash( $_GET['ct_el'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$out    = array( 'action' => $action );

	if ( 'types' === $action ) {
		$out['elementor']    = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null;
		$out['wp']           = get_bloginfo( 'version' );
		$out['experiments']  = get_option( 'elementor_experiment' );
		$out['mcp_enabled']  = get_option( 'elementor_mcp_enabled' );
		$wm                  = \Elementor\Plugin::$instance->widgets_manager;
		$all                 = array_keys( $wm->get_widget_types() );
		$out['atomic_types'] = array_values( array_filter( $all, function ( $t ) { return 0 === strpos( $t, 'e-' ); } ) );
		$out['classic']      = array_values( array_filter( $all, function ( $t ) { return 0 !== strpos( $t, 'e-' ); } ) );
		$out['elements']     = array_keys( \Elementor\Plugin::$instance->elements_manager->get_element_types() );
		$dirs                = glob( WP_PLUGIN_DIR . '/elementor/modules/atomic-widgets/elements/*', GLOB_ONLYDIR );
		$out['source_dirs']  = $dirs ? array_map( 'basename', $dirs ) : array();
		$out['php']          = PHP_VERSION;
		$out['caps']         = class_exists( '\WP_Application_Passwords' );
	}

	if ( 'creds' === $action ) {
		$uid = 0;
		foreach ( get_users( array( 'role' => 'administrator', 'number' => 5 ) ) as $u ) {
			$uid = $u->ID;
			$out['user'] = $u->user_login;
			break;
		}
		if ( ! $uid ) {
			$out['error'] = 'no administrator found';
		} else {
			$res = \WP_Application_Passwords::create_new_application_password( $uid, array( 'name' => CT_ELP_APP ) );
			if ( is_wp_error( $res ) ) {
				$out['error'] = $res->get_error_message();
			} else {
				$out['login']    = $out['user'];
				$out['password'] = $res[0];
				$out['mcp_url']  = rest_url( 'elementor/mcp/' );
			}
		}
	}

	if ( 'revoke' === $action ) {
		$n = 0;
		foreach ( get_users( array( 'role' => 'administrator', 'number' => 5 ) ) as $u ) {
			foreach ( (array) \WP_Application_Passwords::get_user_application_passwords( $u->ID ) as $p ) {
				if ( CT_ELP_APP === $p['name'] ) {
					\WP_Application_Passwords::delete_application_password( $u->ID, $p['uuid'] );
					$n++;
				}
			}
		}
		$out['revoked'] = $n;
	}

	header( 'Content-Type: application/json; charset=utf-8' );
	echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'init', 'ct_elp_router', 97 );
