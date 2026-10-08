<?php
/**
 * Global header — reproduces the Stitch design's fixed navigation bar.
 *
 * @package ConfidentTrader
 */

$ct_nav = array(
	'/live-trading-floor/' => __( 'Trading Floor', 'confident-trader' ),
	'/track-record/'       => __( 'Track Record', 'confident-trader' ),
	'/curriculum/'         => __( 'Curriculum', 'confident-trader' ),
	'/mentors/'            => __( 'Mentors', 'confident-trader' ),
	'/pricing/'            => __( 'Pricing', 'confident-trader' ),
	'/blog/'               => __( 'Insights', 'confident-trader' ),
);

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#0a0d14">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:rounded-lg focus:bg-primary-container focus:text-on-primary-container focus:font-bold" href="#ct-main"><?php esc_html_e( 'Skip to content', 'confident-trader' ); ?></a>
<?php
$ct_logo = ct_logo_svg( 'ctlg-h', 'h-8 w-8' );
?>
<header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.4)]">
	<div class="h-20 max-w-7xl mx-auto px-margin flex items-center justify-between gap-space-md">
		<div class="flex items-center gap-space-md shrink-0">
			<a class="flex items-center gap-space-sm group" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Confident Trader — home', 'confident-trader' ); ?>">
				<?php echo $ct_logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG. ?>
				<span class="font-headline-sm text-headline-sm uppercase tracking-tight text-on-surface font-bold">CONFIDENT<span class="text-primary-container">TRADER</span></span>
			</a>
			<div class="hidden xl:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-high/70 border border-outline-variant/30">
				<span class="relative flex h-2 w-2">
					<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
					<span class="relative inline-flex rounded-full h-2 w-2 bg-secondary"></span>
				</span>
				<span class="font-label-caps text-label-caps uppercase tracking-wider text-secondary">LIVE NY4</span>
			</div>
		</div>

		<nav class="hidden lg:flex items-center gap-space-lg" aria-label="<?php esc_attr_e( 'Primary', 'confident-trader' ); ?>">
			<?php foreach ( $ct_nav as $ct_href => $ct_label ) : ?>
				<a class="font-label-caps text-label-caps uppercase tracking-wider transition-colors <?php echo ct_is_current( $ct_href ) ? 'text-primary-container' : 'text-on-surface-variant hover:text-primary-container'; ?>" href="<?php echo esc_url( home_url( $ct_href ) ); ?>"<?php echo ct_is_current( $ct_href ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $ct_label ); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="flex items-center gap-space-sm shrink-0">
			<a class="hidden xl:inline-flex items-center px-space-md py-2 rounded-lg bg-surface-container-high text-on-surface font-label-ui text-label-ui hover:bg-surface-container-highest hover:text-on-surface transition-all" href="<?php echo esc_url( home_url( '/book-a-desk-assessment/' ) ); ?>"><?php esc_html_e( 'Schedule Call', 'confident-trader' ); ?></a>
			<a class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-container text-on-primary-container font-label-ui text-label-ui font-bold shadow-[0_0_24px_rgba(0,242,254,0.35)] hover:bg-primary-fixed hover:text-on-primary-fixed transition-all" href="<?php echo esc_url( home_url( '/apply/' ) ); ?>"><?php esc_html_e( 'Apply Now', 'confident-trader' ); ?></a>
			<button type="button" class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg bg-surface-container-high text-on-surface hover:bg-surface-container-highest transition-colors" data-ct-menu-open aria-controls="ct-mobile-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open navigation menu', 'confident-trader' ); ?>">
				<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
			</button>
		</div>
	</div>
</header>

<div id="ct-mobile-menu" class="fixed inset-0 z-[70] hidden" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site navigation', 'confident-trader' ); ?>" data-ct-menu-panel>
	<div class="absolute inset-0 bg-surface-container-lowest/90 backdrop-blur-xl" data-ct-menu-close></div>
	<div class="relative h-full w-full max-w-sm ml-auto bg-surface-container border-l border-outline-variant/30 shadow-2xl flex flex-col">
		<div class="flex items-center justify-between h-20 px-margin shrink-0 border-b border-outline-variant/20">
			<span class="font-headline-sm text-headline-sm uppercase tracking-tight text-on-surface font-bold">CONFIDENT<span class="text-primary-container">TRADER</span></span>
			<button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-surface-container-high text-on-surface-variant hover:text-on-surface transition-colors" data-ct-menu-close aria-label="<?php esc_attr_e( 'Close navigation menu', 'confident-trader' ); ?>">
				<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
			</button>
		</div>
		<nav class="flex-1 overflow-y-auto px-margin py-space-lg flex flex-col gap-space-xs" aria-label="<?php esc_attr_e( 'Mobile', 'confident-trader' ); ?>">
			<?php
			$ct_mobile = $ct_nav + array(
				'/programs/'                => __( 'Programs', 'confident-trader' ),
				'/contact/'                 => __( 'Contact', 'confident-trader' ),
				'/book-a-desk-assessment/'  => __( 'Book a Desk Assessment', 'confident-trader' ),
			);
			foreach ( $ct_mobile as $ct_href => $ct_label ) :
				?>
				<a class="py-3 px-3 rounded-lg font-headline-sm text-headline-sm transition-colors <?php echo ct_is_current( $ct_href ) ? 'text-primary-container bg-surface-container-high' : 'text-on-surface hover:bg-surface-container-high'; ?>" href="<?php echo esc_url( home_url( $ct_href ) ); ?>"<?php echo ct_is_current( $ct_href ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $ct_label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="p-margin flex flex-col gap-space-sm shrink-0 border-t border-outline-variant/20">
			<a class="w-full inline-flex items-center justify-center py-3.5 rounded-lg bg-primary-container text-on-primary-container font-label-ui text-label-ui font-bold shadow-[0_0_24px_rgba(0,242,254,0.4)]" href="<?php echo esc_url( home_url( '/apply/' ) ); ?>"><?php esc_html_e( 'Apply for Elite Mentorship', 'confident-trader' ); ?></a>
			<div class="flex items-center justify-center gap-2 text-on-surface-variant font-label-caps text-label-caps uppercase">
				<span class="w-2 h-2 rounded-full bg-secondary"></span>
				<?php esc_html_e( 'Live desk · NY4', 'confident-trader' ); ?>
			</div>
		</div>
	</div>
</div>
