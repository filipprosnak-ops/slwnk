<?php
/**
 * Player single template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$player_id = get_queried_object_id();
$team_id   = absint( get_post_meta( $player_id, '_mahl_team_id', true ) );

get_header();
?>
<main id="primary" class="site-main mahl-single mahl-single-player">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>

			<section class="mahl-player-info">
				<h2><?php esc_html_e( 'Player Information', 'mahl-manager' ); ?></h2>
				<?php if ( ! empty( $team_id ) ) : ?>
					<p>
						<?php esc_html_e( 'Team:', 'mahl-manager' ); ?>
						<a href="<?php echo esc_url( get_permalink( $team_id ) ); ?>"><?php echo esc_html( get_the_title( $team_id ) ); ?></a>
					</p>
				<?php endif; ?>
				<?php if ( get_the_content() ) : ?>
					<div class="entry-content"><?php the_content(); ?></div>
				<?php else : ?>
					<p><?php esc_html_e( 'Basic player information will appear here.', 'mahl-manager' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="mahl-player-statistics-placeholder">
				<h2><?php esc_html_e( 'Statistics', 'mahl-manager' ); ?></h2>
				<p><?php esc_html_e( 'Player statistics will be rendered here by future frontend templates.', 'mahl-manager' ); ?></p>
			</section>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
