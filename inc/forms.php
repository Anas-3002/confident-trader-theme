<?php
/**
 * Native, dependency-free forms: design-styled markup, nonce-checked handler,
 * server-side validation, spam honeypot, audit log (private CPT) and wp_mail.
 *
 * Keeping this in the theme avoids a page-builder form plugin and keeps the
 * design's markup exact.
 *
 * @package ConfidentTrader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form definitions.
 *
 * @return array<string,array{title:string,submit:string,note:string,fields:array}>
 */
function ct_form_definitions() {
	return array(
		'apply'      => array(
			'title'  => __( 'Apply for the next cohort', 'confident-trader' ),
			'note'   => __( 'Applications are reviewed by a senior desk trader. Expect a reply within one business day.', 'confident-trader' ),
			'submit' => __( 'Submit application', 'confident-trader' ),
			'fields' => array(
				array( 'name' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'required' => true ),
				array( 'name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true ),
				array( 'name' => 'country', 'label' => 'Country / timezone', 'type' => 'text', 'required' => true ),
				array( 'name' => 'experience', 'label' => 'Trading experience', 'type' => 'select', 'required' => true, 'options' => array( 'Less than 1 year', '1–3 years', '3–5 years', '5+ years', 'Already funded / prop trader' ) ),
				array( 'name' => 'tier', 'label' => 'Programme of interest', 'type' => 'select', 'required' => true, 'options' => array( 'Execution Core', 'Institutional Mentorship', 'Elite Desk Partner', 'Not sure yet — advise me' ) ),
				array( 'name' => 'instrument', 'label' => 'Primary market', 'type' => 'select', 'required' => false, 'options' => array( 'Index futures (ES/NQ)', 'Gold / Crude (GC/CL)', 'FX', 'Crypto', 'Equities', 'Other' ) ),
				array( 'name' => 'message', 'label' => 'What are you trying to fix?', 'type' => 'textarea', 'required' => true ),
			),
		),
		'assessment' => array(
			'title'  => __( 'Book a 15-minute desk assessment', 'confident-trader' ),
			'note'   => __( 'A short call to map your current process against the desk methodology. No obligation, no sales pressure.', 'confident-trader' ),
			'submit' => __( 'Request assessment slot', 'confident-trader' ),
			'fields' => array(
				array( 'name' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'required' => true ),
				array( 'name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true ),
				array( 'name' => 'timezone', 'label' => 'Timezone', 'type' => 'text', 'required' => true ),
				array( 'name' => 'window', 'label' => 'Preferred window', 'type' => 'select', 'required' => true, 'options' => array( 'Europe / London session', 'US pre-market', 'US cash session', 'Asia / Sydney', 'Flexible' ) ),
				array( 'name' => 'message', 'label' => 'Anything we should look at first?', 'type' => 'textarea', 'required' => false ),
			),
		),
		'contact'    => array(
			'title'  => __( 'Contact the desk', 'confident-trader' ),
			'note'   => __( 'Questions about curriculum, cohorts or the live floor — send them here.', 'confident-trader' ),
			'submit' => __( 'Send message', 'confident-trader' ),
			'fields' => array(
				array( 'name' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'required' => true ),
				array( 'name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true ),
				array( 'name' => 'subject', 'label' => 'Topic', 'type' => 'select', 'required' => true, 'options' => array( 'Curriculum question', 'Cohort availability', 'Billing / enrolment', 'Press or partnership', 'Something else' ) ),
				array( 'name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true ),
			),
		),
	);
}

/**
 * Register the private submissions store.
 */
function ct_register_submission_cpt() {
	register_post_type(
		'ct_submission',
		array(
			'labels'          => array(
				'name'          => __( 'Form submissions', 'confident-trader' ),
				'singular_name' => __( 'Form submission', 'confident-trader' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-email-alt',
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'supports'        => array( 'title', 'editor' ),
		)
	);
}
add_action( 'init', 'ct_register_submission_cpt' );

/**
 * Render a design-styled form.
 *
 * @param string $type Form key.
 * @return string
 */
function ct_form( $type ) {
	$defs = ct_form_definitions();
	if ( ! isset( $defs[ $type ] ) ) {
		return '';
	}
	$def   = $defs[ $type ];
	$state = isset( $_GET['ct_form'] ) ? sanitize_key( wp_unslash( $_GET['ct_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$out  = '<div class="ct-form-shell p-space-lg rounded-xl bg-surface-container border border-outline-variant/30 shadow-2xl">';
	$out .= '<div class="mb-space-lg"><h2 class="font-headline-md text-headline-md text-on-surface font-bold">' . esc_html( $def['title'] ) . '</h2>';
	$out .= '<p class="font-body-md text-body-md text-on-surface-variant mt-1">' . esc_html( $def['note'] ) . '</p></div>';

	if ( 'ok' === $state ) {
		$out .= '<div class="ct-form-success p-space-md rounded-lg bg-secondary/10 border border-secondary/25 flex items-start gap-space-sm" role="status">'
			. ct_icon( 'check_circle', 'text-secondary text-xl' )
			. '<div><span class="font-headline-sm text-label-ui text-secondary font-bold block">' . esc_html__( 'Message received.', 'confident-trader' ) . '</span>'
			. '<p class="font-body-sm text-body-sm text-on-surface-variant">' . esc_html__( 'A member of the desk will reply from our shared inbox. Please check your spam folder if you do not hear back within one business day.', 'confident-trader' ) . '</p></div></div>';
	} elseif ( 'error' === $state ) {
		$out .= '<div class="p-space-md rounded-lg bg-error-container/20 border border-error/30 flex items-start gap-space-sm" role="alert">'
			. ct_icon( 'verified', 'text-error text-xl' )
			. '<div><span class="font-headline-sm text-label-ui text-error font-bold block">' . esc_html__( 'That did not send.', 'confident-trader' ) . '</span>'
			. '<p class="font-body-sm text-body-sm text-on-surface-variant">' . esc_html__( 'Please complete every required field with a valid email address and try again.', 'confident-trader' ) . '</p></div></div>';
	}

	$out .= '<form class="ct-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate>';
	$out .= '<input type="hidden" name="action" value="ct_form">';
	$out .= '<input type="hidden" name="ct_type" value="' . esc_attr( $type ) . '">';
	$out .= wp_nonce_field( 'ct_form_' . $type, 'ct_nonce', true, false );
	$out .= '<div class="hidden" aria-hidden="true"><label>Leave this field empty<input type="text" name="ct_hp" value="" tabindex="-1" autocomplete="off"></label></div>';
	$out .= '<input type="hidden" name="ct_ts" value="' . esc_attr( time() ) . '">';

	foreach ( $def['fields'] as $f ) {
		$id   = 'ct-' . $type . '-' . $f['name'];
		$req  = ! empty( $f['required'] );
		$full = in_array( $f['type'], array( 'textarea' ), true ) ? ' sm:col-span-2' : '';
		$out .= '<div class="flex flex-col gap-1.5' . $full . '">';
		$out .= '<label class="font-label-caps text-label-caps uppercase tracking-wider text-on-surface-variant" for="' . esc_attr( $id ) . '">' . esc_html( $f['label'] ) . ( $req ? ' <span class="text-secondary">*</span>' : ' <span class="text-outline">(' . esc_html__( 'optional', 'confident-trader' ) . ')</span>' ) . '</label>';

		if ( 'textarea' === $f['type'] ) {
			$out .= '<textarea class="ct-input" id="' . esc_attr( $id ) . '" name="' . esc_attr( $f['name'] ) . '" rows="5"' . ( $req ? ' required' : '' ) . '></textarea>';
		} elseif ( 'select' === $f['type'] ) {
			$out .= '<select class="ct-input" id="' . esc_attr( $id ) . '" name="' . esc_attr( $f['name'] ) . '"' . ( $req ? ' required' : '' ) . '>';
			$out .= '<option value="">' . esc_html__( 'Select…', 'confident-trader' ) . '</option>';
			foreach ( $f['options'] as $opt ) {
				$out .= '<option value="' . esc_attr( $opt ) . '">' . esc_html( $opt ) . '</option>';
			}
			$out .= '</select>';
		} else {
			$out .= '<input class="ct-input" type="' . esc_attr( $f['type'] ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $f['name'] ) . '"' . ( $req ? ' required' : '' ) . ' autocomplete="' . esc_attr( 'email' === $f['name'] ? 'email' : ( 'full_name' === $f['name'] ? 'name' : 'on' ) ) . '">';
		}
		$out .= '</div>';
	}

	$out .= '<div class="flex flex-col sm:flex-row items-center gap-space-md pt-space-xs sm:col-span-2">';
	$out .= '<button class="ct-submit w-full sm:w-auto inline-flex items-center justify-center gap-space-sm px-space-lg py-3.5 rounded-lg bg-primary-container text-on-primary-container font-label-ui text-label-ui font-bold shadow-[0_0_28px_rgba(0,242,254,0.4)] hover:bg-primary-fixed hover:text-on-primary-fixed transition-all" type="submit">'
		. '<span class="ct-submit-label">' . esc_html( $def['submit'] ) . '</span>'
		. ct_icon( 'arrow_forward', 'text-lg' )
		. '</button>';
	$out .= '<p class="font-body-sm text-body-sm text-on-surface-variant">' . esc_html__( 'We reply from a monitored inbox. Your details are never sold or shared.', 'confident-trader' ) . '</p>';
	$out .= '</div>';

	$out .= '</form></div>';
	return $out;
}

/**
 * Handle submissions.
 */
function ct_handle_form() {
	$type = isset( $_POST['ct_type'] ) ? sanitize_key( wp_unslash( $_POST['ct_type'] ) ) : '';
	$defs = ct_form_definitions();
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'ct_form' ), $back );

	if ( ! isset( $defs[ $type ] ) ) {
		wp_safe_redirect( add_query_arg( 'ct_form', 'error', $back ) );
		exit;
	}
	if ( ! isset( $_POST['ct_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ct_nonce'] ) ), 'ct_form_' . $type ) ) {
		wp_safe_redirect( add_query_arg( 'ct_form', 'error', $back ) );
		exit;
	}
	// Honeypot + time trap.
	$hp = isset( $_POST['ct_hp'] ) ? trim( (string) wp_unslash( $_POST['ct_hp'] ) ) : '';
	$ts = isset( $_POST['ct_ts'] ) ? (int) $_POST['ct_ts'] : 0;
	if ( '' !== $hp || ( $ts && ( time() - $ts ) < 2 ) ) {
		wp_safe_redirect( add_query_arg( 'ct_form', 'ok', $back ) );
		exit;
	}

	$clean = array();
	foreach ( $defs[ $type ]['fields'] as $f ) {
		$name = $f['name'];
		$val  = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : '';
		$val  = is_string( $val ) ? trim( sanitize_textarea_field( $val ) ) : '';
		if ( ! empty( $f['required'] ) && '' === $val ) {
			wp_safe_redirect( add_query_arg( 'ct_form', 'error', $back ) );
			exit;
		}
		if ( 'email' === $f['type'] && '' !== $val && ! is_email( $val ) ) {
			wp_safe_redirect( add_query_arg( 'ct_form', 'error', $back ) );
			exit;
		}
		if ( 'select' === $f['type'] && '' !== $val && ! in_array( $val, $f['options'], true ) ) {
			$val = '';
		}
		$clean[ $name ] = $val;
	}

	$title = sprintf( '%s — %s', $defs[ $type ]['title'], isset( $clean['full_name'] ) ? $clean['full_name'] : __( 'Unknown', 'confident-trader' ) );
	$body  = '';
	foreach ( $clean as $k => $v ) {
		$body .= ucwords( str_replace( '_', ' ', $k ) ) . ': ' . ( '' === $v ? '—' : $v ) . "\n";
	}
	$body .= "\n---\nSubmitted: " . gmdate( 'c' ) . "\nSource: " . esc_url_raw( $back ) . "\nIP: " . ct_client_ip() . "\n";

	$id = wp_insert_post(
		array(
			'post_type'    => 'ct_submission',
			'post_status'  => 'private',
			'post_title'   => $title,
			'post_content' => $body,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		wp_safe_redirect( add_query_arg( 'ct_form', 'error', $back ) );
		exit;
	}
	foreach ( $clean as $k => $v ) {
		update_post_meta( $id, '_ct_' . $k, $v );
	}
	update_post_meta( $id, '_ct_form_type', $type );

	$to      = ct_notify_email();
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( ! empty( $clean['email'] ) ) {
		$headers[] = 'Reply-To: ' . $clean['email'];
	}
	$sent = wp_mail(
		$to,
		sprintf( '[Confident Trader] %s', $title ),
		$body,
		$headers
	);
	update_post_meta( $id, '_ct_mail', $sent ? 'sent' : 'failed' );

	/**
	 * Fires after a submission is stored.
	 *
	 * @param int    $id    Submission post id.
	 * @param string $type  Form key.
	 * @param array  $clean Sanitised values.
	 */
	do_action( 'ct_form_submitted', $id, $type, $clean );

	wp_safe_redirect( add_query_arg( 'ct_form', 'ok', $back ) . '#ct-form' );
	exit;
}
add_action( 'admin_post_ct_form', 'ct_handle_form' );
add_action( 'admin_post_nopriv_ct_form', 'ct_handle_form' );

/**
 * Where submissions are emailed.
 *
 * @return string
 */
function ct_notify_email() {
	$to = apply_filters( 'ct_notify_email', get_option( 'admin_email' ) );
	return is_email( $to ) ? $to : get_option( 'admin_email' );
}

/**
 * Best-effort client IP for the audit log.
 *
 * @return string
 */
function ct_client_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
			$ip = trim( $ip[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return 'unknown';
}

/**
 * Show the stored fields in the admin list.
 *
 * @param array $columns Columns.
 * @return array
 */
function ct_submission_columns( $columns ) {
	return array(
		'cb'        => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'     => __( 'Submission', 'confident-trader' ),
		'ct_type'   => __( 'Form', 'confident-trader' ),
		'ct_email'  => __( 'Email', 'confident-trader' ),
		'date'      => __( 'Received', 'confident-trader' ),
	);
}
add_filter( 'manage_ct_submission_posts_columns', 'ct_submission_columns' );

/**
 * Render the extra columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post id.
 */
function ct_submission_column_content( $column, $post_id ) {
	if ( 'ct_type' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_ct_form_type', true ) );
	}
	if ( 'ct_email' === $column ) {
		$email = (string) get_post_meta( $post_id, '_ct_email', true );
		echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '&mdash;';
	}
}
add_action( 'manage_ct_submission_posts_custom_column', 'ct_submission_column_content', 10, 2 );
