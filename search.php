<?php
/**
 * Search results.
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
				<span class="font-label-caps text-label-caps text-primary uppercase tracking-widest"><?php esc_html_e( 'Site search', 'confident-trader' ); ?></span>
				<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight mt-1 mb-space-sm">
					<?php
					/* translators: %s: search query. */
					printf( esc_html__( 'Results for “%s”', 'confident-trader' ), esc_html( get_search_query() ) );
					?>
				</h1>
				<form class="mx-auto max-w-xl flex flex-col sm:flex-row gap-space-sm" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="sr-only" for="ct-search-field"><?php esc_html_e( 'Search', 'confident-trader' ); ?></label>
					<input class="ct-input flex-1" id="ct-search-field" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search insights and programmes', 'confident-trader' ); ?>">
					<button class="inline-flex items-center justify-center px-space-md py-3 rounded-lg bg-primary-container text-on-primary-container font-label-ui text-label-ui font-bold" type="submit"><?php esc_html_e( 'Search', 'confident-trader' ); ?></button>
				</form>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="max-w-3xl mx-auto flex flex-col gap-space-md">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article <?php post_class( 'p-space-md rounded-xl bg-surface-container hover:bg-surface-container-high transition-colors' ); ?>>
							<span class="font-label-caps text-label-caps uppercase text-primary-container"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></span>
							<h2 class="font-headline-md text-headline-md text-on-surface font-bold mt-1"><a class="hover:text-primary-container transition-colors" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="font-body-md text-body-md text-on-surface-variant mt-1"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 30, '…' ) ); ?></p>
						</article>
						<?php
					endwhile;
					?>
				</div>
				<div class="mt-space-xl flex justify-center">
					<?php
					$ct_pag = get_the_posts_pagination( array( 'mid_size' => 1, 'class' => 'ct-pagination' ) );
					echo $ct_pag ? wp_kses_post( $ct_pag ) : '';
					?>
				</div>
			<?php else : ?>
				<div class="max-w-3xl mx-auto p-space-lg rounded-xl bg-surface-container text-center">
					<p class="font-body-lg text-body-lg text-on-surface-variant"><?php esc_html_e( 'Nothing matched that search. Try a broader term, or browse the curriculum and insights indexes.', 'confident-trader' ); ?></p>
					<div class="flex flex-col sm:flex-row justify-center gap-space-sm mt-space-md">
						<a class="inline-flex items-center justify-center px-space-md py-3 rounded-lg bg-surface-container-high text-on-surface font-label-ui hover:bg-surface-container-highest transition-colors" href="<?php echo esc_url( home_url( '/curriculum/' ) ); ?>"><?php esc_html_e( 'Browse the curriculum', 'confident-trader' ); ?></a>
						<a class="inline-flex items-center justify-center px-space-md py-3 rounded-lg bg-surface-container-high text-on-surface font-label-ui hover:bg-surface-container-highest transition-colors" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Read the insights', 'confident-trader' ); ?></a>
					</div>
				</div>
			<?php endif; ?>
		</section>
	</div>
</main>
<?php
get_footer();
