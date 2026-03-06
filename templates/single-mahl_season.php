<?php
/**
 * Season single template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$season_id = get_queried_object_id();
$phases    = get_posts(
	array(
		'post_type'      => 'mahl_phase',
		'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => '_mahl_season_id',
				'value'   => $season_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			),
		), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	)
);

get_header();
?>
<main id="primary" class="site-main mahl-single mahl-single-season">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>

			<?php if ( has_excerpt() ) : ?>
				<div class="entry-summary"><?php the_excerpt(); ?></div>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="entry-content"><?php the_content(); ?></div>
			<?php endif; ?>

			<section class="mahl-season-phases">
				<h2><?php esc_html_e( 'Season Phases', 'mahl-manager' ); ?></h2>
				<?php if ( ! empty( $phases ) ) : ?>
					<ul class="mahl-entry-list">
						<?php foreach ( $phases as $phase ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $phase ) ); ?>"><?php echo esc_html( get_the_title( $phase ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'No phases have been assigned to this season yet.', 'mahl-manager' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="mahl-season-standings-placeholder">
				<h2><?php esc_html_e( 'Standings', 'mahl-manager' ); ?></h2>
				<p><?php esc_html_e( 'Season standings will be rendered here by future frontend templates.', 'mahl-manager' ); ?></p>
			</section>

			<section class="mahl-season-games-placeholder">
				<h2><?php esc_html_e( 'Games', 'mahl-manager' ); ?></h2>
				<p><?php esc_html_e( 'Season game listings will be rendered here by future frontend templates.', 'mahl-manager' ); ?></p>
			</section>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
