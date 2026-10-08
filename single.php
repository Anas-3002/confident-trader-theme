<?php
/**
 * Single insight article.
 *
 * @package ConfidentTrader
 */

get_header();
?>
<main class="w-full pt-28 bg-background flex-1">
	<div class="relative w-full overflow-hidden">
		<?php get_template_part( 'template-parts/glows' ); ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'max-w-4xl mx-auto px-margin py-space-xl relative z-10' ); ?>>
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
				<header class="mt-space-lg mb-space-lg">
					<?php
					$cats = get_the_category();
					if ( $cats ) :
						?>
						<a class="px-2 py-0.5 rounded bg-primary-container/10 text-primary-container font-label-caps text-label-caps uppercase font-bold" href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
					<?php endif; ?>
					<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight mt-space-sm leading-tight"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed mt-space-sm"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<div class="flex flex-wrap items-center gap-space-md mt-space-md font-label-caps text-label-caps uppercase text-outline">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<span class="text-outline-variant" aria-hidden="true">&bull;</span>
						<span><?php echo esc_html( ct_read_time() ); ?></span>
						<span class="text-outline-variant" aria-hidden="true">&bull;</span>
						<span class="text-secondary"><?php esc_html_e( 'Confident Trader desk', 'confident-trader' ); ?></span>
					</div>
				</header>

				<div class="ct-prose p-space-lg rounded-xl bg-surface-container border border-outline-variant/20 shadow-lg">
					<?php the_content(); ?>
				</div>

				<?php
				$tags = get_the_tags();
				if ( $tags ) :
					?>
					<div class="flex flex-wrap items-center gap-2 mt-space-md">
						<?php foreach ( $tags as $tag ) : ?>
							<a class="px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant font-label-caps text-label-caps uppercase hover:text-on-surface transition-colors" href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"><?php echo esc_html( $tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</article>
			<?php
			// Related insights in the same category.
			$ct_terms = wp_get_post_categories( get_the_ID() );
			if ( $ct_terms ) :
				$ct_related = new WP_Query(
					array(
						'post__not_in'        => array( get_the_ID() ),
						'posts_per_page'      => 3,
						'category__in'        => $ct_terms,
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
					)
				);
				if ( $ct_related->have_posts() ) :
					?>
					<section class="max-w-7xl mx-auto px-margin pb-space-xl" aria-label="<?php esc_attr_e( 'Related insights', 'confident-trader' ); ?>">
						<h2 class="font-headline-md text-headline-md text-on-surface font-bold mb-space-md"><?php esc_html_e( 'Continue reading', 'confident-trader' ); ?></h2>
						<div class="grid grid-cols-1 md:grid-cols-3 gap-space-lg">
							<?php
							while ( $ct_related->have_posts() ) :
								$ct_related->the_post();
								?>
								<article <?php post_class( 'p-space-md rounded-xl bg-surface-container hover:bg-surface-container-high transition-colors' ); ?>>
									<h3 class="font-headline-sm text-headline-sm text-on-surface font-bold leading-snug"><a class="hover:text-primary-container transition-colors" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p class="font-body-sm text-body-sm text-on-surface-variant mt-1"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 18, '…' ) ); ?></p>
								</article>
								<?php
							endwhile;
							?>
						</div>
					</section>
					<?php
				endif;
				wp_reset_postdata();
			endif;
			?>
		<?php endwhile; ?>
	</div>
</main>
<?php
get_footer();
