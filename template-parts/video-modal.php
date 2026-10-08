<?php
/**
 * Desk-breakdown modal (the design's video popup).
 *
 * The Stitch export opened a fake video player. There is no recorded asset, so
 * this renders the agenda the desk breakdown covers — no fabricated media.
 *
 * @package ConfidentTrader
 */
?>
<div class="fixed inset-0 z-50 bg-surface-container-lowest/90 backdrop-blur-xl flex items-center justify-center p-margin hidden" id="video-breakdown-modal" role="dialog" aria-modal="true" aria-labelledby="ct-breakdown-title" data-ct-modal>
	<div class="bg-surface-container rounded-2xl max-w-3xl w-full p-space-lg shadow-2xl relative flex flex-col gap-space-md max-h-[90vh] overflow-y-auto">
		<div class="flex items-center justify-between gap-space-md">
			<div class="flex items-center gap-2">
				<span class="w-2.5 h-2.5 rounded-full bg-secondary animate-pulse"></span>
				<span class="font-headline-sm text-headline-sm text-on-surface font-bold" id="ct-breakdown-title"><?php esc_html_e( 'Inside a live desk session', 'confident-trader' ); ?></span>
			</div>
			<button type="button" class="p-1 rounded-full bg-surface-container-high text-on-surface-variant hover:text-on-surface" data-ct-modal-close aria-label="<?php esc_attr_e( 'Close', 'confident-trader' ); ?>">
				<?php echo ct_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
		<div class="relative w-full rounded-xl bg-surface-container-lowest border border-outline-variant/20 p-space-md overflow-hidden">
			<div class="absolute inset-0 bg-gradient-to-tr from-surface-container-lowest via-surface-container to-surface-container-high opacity-90 pointer-events-none"></div>
			<ul class="relative z-10 flex flex-col gap-space-sm font-body-md text-body-md text-on-surface-variant">
				<li class="flex items-start gap-2"><?php echo ct_icon( 'check_circle', 'text-secondary text-sm mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><strong class="text-on-surface">08:15 ET — Pre-market prep.</strong> Overnight range, session extremes, volume composite and the levels that actually matter for the day.</span></li>
				<li class="flex items-start gap-2"><?php echo ct_icon( 'check_circle', 'text-secondary text-sm mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><strong class="text-on-surface">09:30 ET — Open auction read.</strong> Reading the first balance area, where passive liquidity is resting, and what would invalidate the idea.</span></li>
				<li class="flex items-start gap-2"><?php echo ct_icon( 'check_circle', 'text-secondary text-sm mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><strong class="text-on-surface">Execution and management.</strong> Entry trigger, stop placement that respects market structure, scale-out plan and the trade journal entry.</span></li>
				<li class="flex items-start gap-2"><?php echo ct_icon( 'check_circle', 'text-secondary text-sm mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><strong class="text-on-surface">Midday review.</strong> What worked, what was noise, and the one process change to carry into the next session.</span></li>
			</ul>
		</div>
		<p class="font-body-sm text-body-sm text-on-surface-variant"><?php esc_html_e( 'Recorded sessions are released to enrolled traders inside the member area. Anyone can read the full session structure first.', 'confident-trader' ); ?></p>
		<div class="flex flex-col sm:flex-row gap-space-sm">
			<a class="inline-flex items-center justify-center gap-space-sm px-space-md py-3 rounded-lg bg-primary-container text-on-primary-container font-label-ui text-label-ui font-bold shadow-[0_0_24px_rgba(0,242,254,0.4)]" href="<?php echo esc_url( home_url( '/live-trading-floor/' ) ); ?>"><?php esc_html_e( 'See the full session structure', 'confident-trader' ); ?> <?php echo ct_icon( 'arrow_forward', 'text-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			<a class="inline-flex items-center justify-center gap-space-sm px-space-md py-3 rounded-lg bg-surface-container-high text-on-surface font-label-ui text-label-ui hover:bg-surface-container-highest transition-colors" href="<?php echo esc_url( home_url( '/book-a-desk-assessment/' ) ); ?>"><?php esc_html_e( 'Book a 15-min assessment', 'confident-trader' ); ?></a>
		</div>
	</div>
</div>
