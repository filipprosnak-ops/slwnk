<?php
/**
 * Season archive template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main mahl-archive mahl-archive-season">
	<header class="page-header">
		<h1 class="page-title"><?php post_type_archive_title(); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<ul class="mahl-entry-list">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<li class="mahl-entry-item">
					<h2 class="mahl-entry-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<?php if ( has_excerpt() ) : ?>
						<div class="mahl-entry-summary"><?php the_excerpt(); ?></div>
					<?php endif; ?>
				</li>
			<?php endwhile; ?>
		</ul>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No seasons found.', 'mahl-manager' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
