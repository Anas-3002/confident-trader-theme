<?php
/**
 * Fallback template.
 *
 * @package ConfidentTrader
 */

get_header();
?>
<main id="ct-main" class="w-full pt-28 bg-background flex-1">
	<div class="relative w-full overflow-hidden">
		<?php get_template_part( 'template-parts/glows' ); ?>
		<section class="max-w-4xl mx-auto px-margin py-space-xl relative z-10">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight mt-space-lg mb-space-lg"><?php echo esc_html( is_home() ? __( 'Insights', 'confident-trader' ) : get_the_title() ); ?></h1>
			<div class="ct-prose">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</section>
	</div>
</main>
<?php
get_footer();
