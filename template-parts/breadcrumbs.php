<?php
/**
 * Visual breadcrumb trail (schema is emitted in inc/seo.php).
 *
 * @package ConfidentTrader
 */
if ( is_front_page() ) {
	return;
}
$trail = array( array( 'label' => __( 'Home', 'confident-trader' ), 'url' => home_url( '/' ) ) );
if ( is_singular( 'post' ) ) {
	$trail[] = array( 'label' => __( 'Insights', 'confident-trader' ), 'url' => home_url( '/blog/' ) );
}
if ( is_search() ) {
	$trail[] = array( 'label' => __( 'Search', 'confident-trader' ), 'url' => home_url( '/?s=' . rawurlencode( get_search_query() ) ) );
} elseif ( is_home() ) {
	$trail[] = array( 'label' => __( 'Insights', 'confident-trader' ), 'url' => home_url( '/blog/' ) );
} elseif ( is_category() || is_tag() || is_archive() ) {
	$trail[] = array( 'label' => wp_strip_all_tags( get_the_archive_title() ), 'url' => '' );
} elseif ( is_singular() ) {
	$trail[] = array( 'label' => get_the_title(), 'url' => get_permalink() );
} elseif ( is_404() ) {
	$trail[] = array( 'label' => __( 'Not found', 'confident-trader' ), 'url' => '' );
}
?>
<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'confident-trader' ); ?>" class="font-label-caps text-label-caps uppercase tracking-wider">
	<ol class="flex flex-wrap items-center gap-2 text-on-surface-variant">
		<?php
		$last = count( $trail ) - 1;
		foreach ( $trail as $i => $crumb ) :
			?>
			<li class="flex items-center gap-2">
				<?php if ( $i !== $last && $crumb['url'] ) : ?>
					<a class="hover:text-primary-container transition-colors" href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
					<span class="text-outline-variant" aria-hidden="true">/</span>
				<?php else : ?>
					<span class="text-on-surface" aria-current="page"><?php echo esc_html( $crumb['label'] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
