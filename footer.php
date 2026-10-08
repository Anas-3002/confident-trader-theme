<?php
/**
 * Global footer — reproduces the Stitch design's four-column footer.
 *
 * @package ConfidentTrader
 */
?>
<footer class="w-full bg-surface-container-lowest py-space-xl">
	<div class="max-w-7xl mx-auto px-margin flex flex-col gap-space-xl">
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg lg:gap-space-xl pb-space-lg">
			<div class="flex flex-col gap-space-md sm:col-span-2 lg:col-span-1">
				<a class="flex items-center gap-space-sm" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Confident Trader — home', 'confident-trader' ); ?>">
					<?php echo ct_logo_svg( 'ctlg-f', 'h-7 w-7' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG. ?>
					<span class="font-headline-sm text-headline-sm uppercase tracking-tight text-on-surface font-bold">CONFIDENT<span class="text-primary-container">TRADER</span></span>
				</a>
				<p class="font-body-sm text-body-sm text-on-surface-variant">Institutional order execution education, mathematical risk validation, and live trading-floor mentorship for serious independent traders and funded capital allocators.</p>
				<div class="flex items-center gap-space-sm pt-space-xs">
					<span class="w-2 h-2 rounded-full bg-secondary"></span>
					<span class="font-label-caps text-label-caps text-secondary uppercase">Live Market Sessions Mon–Fri</span>
				</div>
			</div>

			<div class="flex flex-col gap-space-sm">
				<span class="font-label-caps text-label-caps uppercase text-on-surface tracking-wider"><?php esc_html_e( 'Curriculum & Desks', 'confident-trader' ); ?></span>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/order-flow-mastery/' ) ); ?>">Order Flow Mastery</a>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/live-trading-floor/' ) ); ?>">London &amp; NY Execution Floor</a>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/risk-engine/' ) ); ?>">Risk Engine &amp; Position Sizing</a>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/prop-capital-path/' ) ); ?>">Prop Capital Path</a>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/curriculum/' ) ); ?>">Full Masterclass Architecture</a>
			</div>

			<div class="flex flex-col gap-space-sm">
				<span class="font-label-caps text-label-caps uppercase text-on-surface tracking-wider"><?php esc_html_e( 'Governance & Auditing', 'confident-trader' ); ?></span>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/track-record/' ) ); ?>">Third-Party Broker Verified</a>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/risk-disclosures/' ) ); ?>">Trader Risk Benchmarks</a>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Desk Allocation Protocol</a>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/terms-of-execution/' ) ); ?>">Execution Terms</a>
				<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/risk-disclosures/' ) ); ?>">CFTC Rule 4.41 Statement</a>
			</div>

			<div class="flex flex-col gap-space-sm">
				<span class="font-label-caps text-label-caps uppercase text-on-surface tracking-wider"><?php esc_html_e( 'Institutional Verification', 'confident-trader' ); ?></span>
				<div class="p-space-md rounded-lg bg-surface-container-low flex flex-col gap-space-xs">
					<span class="font-label-caps text-label-caps text-secondary uppercase">Audited Track Record Note</span>
					<p class="font-body-sm text-body-sm text-on-surface-variant">Displayed performance telemetry is illustrative of the desk methodology and is not a promise of future results. Trading involves substantial risk of loss.</p>
				</div>
				<div class="flex flex-col gap-space-sm pt-space-xs">
					<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Insights &amp; Research</a>
					<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/programs/' ) ); ?>">Programs Overview</a>
					<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/mentors/' ) ); ?>">The Desk &amp; Mentors</a>
					<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact the Desk</a>
					<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/apply/' ) ); ?>">Apply for Cohorts</a>
				</div>
			</div>
		</div>

		<div class="flex flex-col gap-space-md pt-space-lg border-t border-outline-variant/20">
			<p class="font-body-sm text-body-sm text-outline leading-relaxed">Regulatory Compliance Disclaimer: Trading financial instruments, futures, foreign exchange, and equities involves substantial risk of loss and is not suitable for every investor. Valuation of assets may fluctuate, and investors may lose more than their initial investment. ConfidentTrader provides educational content, quantitative curriculum, and professional mentorship. Nothing on this site is investment advice, a solicitation, or a guarantee of performance. Past performance is not indicative of future results.</p>
			<div class="flex flex-col sm:flex-row items-center justify-between gap-space-md font-body-sm text-body-sm text-on-surface-variant">
				<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> ConfidentTrader. All rights reserved.</p>
				<div class="flex items-center gap-space-lg">
					<a class="text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a>
					<a class="text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/terms-of-execution/' ) ); ?>">Terms of Execution</a>
					<a class="text-on-surface-variant hover:text-on-surface transition-colors" href="<?php echo esc_url( home_url( '/risk-disclosures/' ) ); ?>">Risk Disclosures</a>
				</div>
			</div>
		</div>
	</div>
</footer>

<?php get_template_part( 'template-parts/video-modal' ); ?>
<?php wp_footer(); ?>
</body>
</html>
