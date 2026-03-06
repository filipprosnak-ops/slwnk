<?php
/**
 * Team single template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$team_id    = get_queried_object_id();
$season_id  = absint( get_post_meta( $team_id, '_mahl_season_id', true ) );
$roster     = get_posts(
	array(
		'post_type'      => 'mahl_player',
		'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => '_mahl_team_id',
				'value'   => $team_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			),
		), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	)
);

get_header();
?>
<main id="primary" class="site-main mahl-single mahl-single-team">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>

			<section class="mahl-team-info">
				<h2><?php esc_html_e( 'Team Information', 'mahl-manager' ); ?></h2>
				<?php if ( ! empty( $season_id ) ) : ?>
					<p>
						<?php esc_html_e( 'Season:', 'mahl-manager' ); ?>
						<a href="<?php echo esc_url( get_permalink( $season_id ) ); ?>"><?php echo esc_html( get_the_title( $season_id ) ); ?></a>
					</p>
				<?php endif; ?>
				<?php if ( get_the_content() ) : ?>
					<div class="entry-content"><?php the_content(); ?></div>
				<?php else : ?>
					<p><?php esc_html_e( 'Basic team information will appear here.', 'mahl-manager' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="mahl-team-roster-placeholder">
				<h2><?php esc_html_e( 'Player Roster', 'mahl-manager' ); ?></h2>
				<?php if ( ! empty( $roster ) ) : ?>
					<ul class="mahl-entry-list">
						<?php foreach ( $roster as $player ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $player ) ); ?>"><?php echo esc_html( get_the_title( $player ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'No players are assigned to this team yet.', 'mahl-manager' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="mahl-team-games-placeholder">
				<h2><?php esc_html_e( 'Games', 'mahl-manager' ); ?></h2>
				<p><?php esc_html_e( 'Team game listings will be rendered here by future frontend templates.', 'mahl-manager' ); ?></p>
			</section>

			<section class="mahl-team-standings-context-placeholder">
				<h2><?php esc_html_e( 'Standings Context', 'mahl-manager' ); ?></h2>
				<p><?php esc_html_e( 'Team standings context will be rendered here by future frontend templates.', 'mahl-manager' ); ?></p>
			</section>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
