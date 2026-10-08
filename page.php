<?php
/**
 * Default page template. Managed design pages carry their own <section> markup.
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
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'ct-page' ); ?>>
				<?php if ( ! get_post_meta( get_the_ID(), '_ct_managed', true ) ) : ?>
					<header class="max-w-7xl mx-auto px-margin pt-space-lg">
						<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
						<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight mt-2"><?php the_title(); ?></h1>
					</header>
				<?php endif; ?>
				<?php the_content(); ?>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
