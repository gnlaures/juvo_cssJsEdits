<?php
/**
 * Template Name: Avantio Single Template
 *
 * This template has width, margin and padding containers removed for use with page builder plugins.
 *
 * @package Genesis Block Theme
 */

get_header(); ?>

	<?php //<div id="primary" class="content-area"> ?>
	<div class="content-area">
		<main id="main" class="site-main">

			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			?>
			<?php endwhile; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

	<?php get_footer(); ?>
