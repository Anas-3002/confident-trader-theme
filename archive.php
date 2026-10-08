<?php
/**
 * Insights archive (blog index, categories, tags, date archives).
 *
 * @package ConfidentTrader
 */

get_header();
?>
<main id="ct-main" class="w-full pt-28 bg-background flex-1">
	<div class="relative w-full overflow-hidden">
		<?php get_template_part( 'template-parts/glows' ); ?>
		<section class="max-w-7xl mx-auto px-margin py-space-xl relative z-10">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<div class="text-center max-w-3xl mx-auto mb-space-xl mt-space-lg">
				<span class="font-label-caps text-label-caps text-primary uppercase tracking-widest"><?php esc_html_e( 'Desk research &amp; field notes', 'confident-trader' ); ?></span>
				<?php if ( is_home() ) : ?>
					<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight mt-1 mb-space-sm">Insights From The Execution Floor</h1>
					<p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">Practical writing on auction market theory, order-flow mechanics, risk engineering and prop-firm evaluation rules — written for traders who are past the indicator stage.</p>
				<?php else : ?>
					<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight mt-1 mb-space-sm"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
					<?php
					$desc = get_the_archive_description();
					if ( $desc ) :
						?>
						<p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed"><?php echo wp_kses_post( $desc ); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'p-space-lg rounded-xl bg-surface-container shadow-lg flex flex-col justify-between hover:-translate-y-1 hover:bg-surface-container-high transition-all duration-300' ); ?>>
							<div class="flex flex-col gap-space-sm">
								<div class="flex items-center justify-between gap-2">
									<?php
									$cats = get_the_category();
									if ( $cats ) :
										?>
										<a class="px-2 py-0.5 rounded bg-primary-container/10 text-primary-container font-label-caps text-label-caps uppercase font-bold hover:bg-primary-container/20 transition-colors" href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
									<?php endif; ?>
									<span class="font-label-caps text-label-caps uppercase text-outline"><?php echo esc_html( ct_read_time() ); ?></span>
								</div>
								<h2 class="font-headline-md text-headline-md text-on-surface font-bold leading-snug">
									<a class="hover:text-primary-container transition-colors" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h2>
								<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
							</div>
							<div class="pt-space-md mt-space-md flex items-center justify-between border-t border-outline-variant/20">
								<time class="font-label-caps text-label-caps uppercase text-outline" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<a class="inline-flex items-center gap-1 text-primary-container font-label-caps text-label-caps uppercase font-bold hover:gap-2 transition-all" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read', 'confident-trader' ); ?> <?php echo ct_icon( 'arrow_forward', 'text-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
							</div>
						</article>
						<?php
					endwhile;
					?>
				</div>

				<?php
				$paged = get_the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => '<span class="material-symbols-outlined text-sm" aria-hidden="true">expand_more</span><span class="sr-only">Previous</span><span aria-hidden="true">Newer</span>',
						'next_text' => '<span aria-hidden="true">Older</span><span class="sr-only">Next</span><span class="material-symbols-outlined text-sm rotate-[-90deg]" aria-hidden="true">expand_more</span>',
						'class'     => 'ct-pagination',
					)
				);
				if ( $paged ) :
					echo '<div class="mt-space-xl flex justify-center">' . $paged . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup.
				endif;
				?>
			<?php else : ?>
				<div class="max-w-3xl mx-auto p-space-lg rounded-xl bg-surface-container text-center">
					<p class="font-body-lg text-body-lg text-on-surface-variant"><?php esc_html_e( 'No articles published under this topic yet.', 'confident-trader' ); ?></p>
					<a class="inline-flex items-center gap-space-sm mt-space-md px-space-md py-3 rounded-lg bg-surface-container-high text-on-surface font-label-ui text-label-ui hover:bg-surface-container-highest transition-colors" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Back to all insights', 'confident-trader' ); ?></a>
				</div>
			<?php endif; ?>
		</section>
	</div>
</main>
<?php
get_footer();
