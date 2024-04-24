<?php
/**
 * Template Name: Avantio Search Template
 *
 * This template has width, margin and padding containers removed for use with page builder plugins.
 *
 * @package Genesis Block Theme
 */

/*if (function_exists('elementor_header')) {
    elementor_header();
} else {
	get_header();
}*/
get_header(); ?>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-content' ); ?>>
		<?php
		$hide_title = get_post_meta( get_the_ID(), '_genesis_block_theme_hide_title', true );
		if ( ! $hide_title ) {
			?>
				<header class="entry-header">
					<h1 class="entry-title">
					<?php the_title(); ?>
					</h1>
				</header>
				<?php
		} // End if hide title
			the_content();
		?>
		</article>
	<?php endwhile; ?>
<?php
get_footer();
/*if (function_exists('elementor_footer')) {
    elementor_footer();
} else {
	get_footer();
}*/ ?>
