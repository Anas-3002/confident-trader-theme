<?php
/**
 * 404 page.
 *
 * @package ConfidentTrader
 */

get_header();
?>
<main id="ct-main" class="w-full pt-28 bg-background flex-1">
	<div class="relative w-full overflow-hidden">
		<?php get_template_part( 'template-parts/glows' ); ?>
		<section class="max-w-3xl mx-auto px-margin py-space-xl relative z-10 text-center">
			<span class="font-label-caps text-label-caps text-error uppercase tracking-widest">404 &mdash; <?php esc_html_e( 'Level not found', 'confident-trader' ); ?></span>
			<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight mt-2 mb-space-sm"><?php esc_html_e( 'This level was never mapped on the ladder.', 'confident-trader' ); ?></h1>
			<p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed"><?php esc_html_e( 'The page you asked for is not here. Every page on this site is reachable from the navigation — or start from one of the desks below.', 'confident-trader' ); ?></p>
			<div class="p-space-lg rounded-xl bg-surface-container border border-outline-variant/20 mt-space-xl text-left">
				<span class="font-label-caps text-label-caps text-secondary uppercase tracking-widest"><?php esc_html_e( 'Popular destinations', 'confident-trader' ); ?></span>
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-space-sm mt-space-md">
					<a class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container-high transition-colors font-body-md text-on-surface" href="<?php echo esc_url( home_url( '/curriculum/' ) ); ?>"><?php esc_html_e( 'Masterclass curriculum', 'confident-trader' ); ?> <?php echo ct_icon( 'arrow_forward', 'text-sm text-primary-container' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<a class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container-high transition-colors font-body-md text-on-surface" href="<?php echo esc_url( home_url( '/live-trading-floor/' ) ); ?>"><?php esc_html_e( 'Live execution floor', 'confident-trader' ); ?> <?php echo ct_icon( 'arrow_forward', 'text-sm text-primary-container' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<a class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container-high transition-colors font-body-md text-on-surface" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Programmes &amp; pricing', 'confident-trader' ); ?> <?php echo ct_icon( 'arrow_forward', 'text-sm text-primary-container' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<a class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container-high transition-colors font-body-md text-on-surface" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Insights &amp; research', 'confident-trader' ); ?> <?php echo ct_icon( 'arrow_forward', 'text-sm text-primary-container' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<a class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container-high transition-colors font-body-md text-on-surface" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact the desk', 'confident-trader' ); ?> <?php echo ct_icon( 'arrow_forward', 'text-sm text-primary-container' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<a class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container-high transition-colors font-body-md text-on-surface" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'confident-trader' ); ?> <?php echo ct_icon( 'arrow_forward', 'text-sm text-primary-container' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</div>
			</div>
			<form class="mt-space-lg mx-auto max-w-xl flex flex-col sm:flex-row gap-space-sm" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="sr-only" for="ct-404-search"><?php esc_html_e( 'Search', 'confident-trader' ); ?></label>
				<input class="ct-input flex-1" id="ct-404-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Search the site', 'confident-trader' ); ?>">
				<button class="inline-flex items-center justify-center px-space-md py-3 rounded-lg bg-primary-container text-on-primary-container font-label-ui font-bold" type="submit"><?php esc_html_e( 'Search', 'confident-trader' ); ?></button>
			</form>
		</section>
	</div>
</main>
<?php
get_footer();
