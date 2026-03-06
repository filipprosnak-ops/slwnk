<?php
/**
 * Game single template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$game_id           = get_queried_object_id();
$season_id         = absint( get_post_meta( $game_id, '_mahl_season_id', true ) );
$phase_id          = absint( get_post_meta( $game_id, '_mahl_phase_id', true ) );
$round_number      = absint( get_post_meta( $game_id, '_mahl_round_number', true ) );
$group_key         = get_post_meta( $game_id, '_mahl_group_key', true );
$home_team_id      = absint( get_post_meta( $game_id, '_mahl_home_team_id', true ) );
$away_team_id      = absint( get_post_meta( $game_id, '_mahl_away_team_id', true ) );
$home_score_final  = get_post_meta( $game_id, '_mahl_score_home_final', true );
$away_score_final  = get_post_meta( $game_id, '_mahl_score_away_final', true );
$status            = get_post_meta( $game_id, '_mahl_status', true );
$stored_events     = get_post_meta( $game_id, '_mahl_game_events', true );
$stored_events     = is_array( $stored_events ) ? $stored_events : array();

get_header();
?>
<main id="primary" class="site-main mahl-single mahl-single-game">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>

			<section class="mahl-game-summary">
				<h2><?php esc_html_e( 'Game Summary', 'mahl-manager' ); ?></h2>
				<dl class="mahl-definition-list">
					<?php if ( ! empty( $season_id ) ) : ?>
						<dt><?php esc_html_e( 'Season', 'mahl-manager' ); ?></dt>
						<dd><a href="<?php echo esc_url( get_permalink( $season_id ) ); ?>"><?php echo esc_html( get_the_title( $season_id ) ); ?></a></dd>
					<?php endif; ?>

					<?php if ( ! empty( $phase_id ) ) : ?>
						<dt><?php esc_html_e( 'Phase', 'mahl-manager' ); ?></dt>
						<dd><a href="<?php echo esc_url( get_permalink( $phase_id ) ); ?>"><?php echo esc_html( get_the_title( $phase_id ) ); ?></a></dd>
					<?php endif; ?>

					<?php if ( ! empty( $round_number ) ) : ?>
						<dt><?php esc_html_e( 'Round', 'mahl-manager' ); ?></dt>
						<dd><?php echo esc_html( $round_number ); ?></dd>
					<?php endif; ?>

					<?php if ( ! empty( $group_key ) ) : ?>
						<dt><?php esc_html_e( 'Group / Bracket', 'mahl-manager' ); ?></dt>
						<dd><?php echo esc_html( ucwords( str_replace( '-', ' ', $group_key ) ) ); ?></dd>
					<?php endif; ?>

					<dt><?php esc_html_e( 'Home Team', 'mahl-manager' ); ?></dt>
					<dd>
						<?php if ( ! empty( $home_team_id ) ) : ?>
							<a href="<?php echo esc_url( get_permalink( $home_team_id ) ); ?>"><?php echo esc_html( get_the_title( $home_team_id ) ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'Not assigned', 'mahl-manager' ); ?>
						<?php endif; ?>
					</dd>

					<dt><?php esc_html_e( 'Away Team', 'mahl-manager' ); ?></dt>
					<dd>
						<?php if ( ! empty( $away_team_id ) ) : ?>
							<a href="<?php echo esc_url( get_permalink( $away_team_id ) ); ?>"><?php echo esc_html( get_the_title( $away_team_id ) ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'Not assigned', 'mahl-manager' ); ?>
						<?php endif; ?>
					</dd>

					<dt><?php esc_html_e( 'Score', 'mahl-manager' ); ?></dt>
					<dd>
						<?php
						if ( '' !== $home_score_final && '' !== $away_score_final ) {
							echo esc_html( $home_score_final . ' : ' . $away_score_final );
						} else {
							esc_html_e( 'Not available', 'mahl-manager' );
						}
						?>
					</dd>

					<?php if ( ! empty( $status ) ) : ?>
						<dt><?php esc_html_e( 'Status', 'mahl-manager' ); ?></dt>
						<dd><?php echo esc_html( ucwords( str_replace( '_', ' ', $status ) ) ); ?></dd>
					<?php endif; ?>
				</dl>
			</section>

			<?php if ( get_the_content() ) : ?>
				<div class="entry-content"><?php the_content(); ?></div>
			<?php endif; ?>

			<section class="mahl-game-events-placeholder">
				<h2><?php esc_html_e( 'Game Events', 'mahl-manager' ); ?></h2>
				<?php if ( ! empty( $stored_events ) ) : ?>
					<p>
						<?php
						printf(
							/* translators: %d: number of stored events. */
							esc_html__( 'This game currently has %d stored event entries. Detailed event rendering will be added in future frontend templates.', 'mahl-manager' ),
							count( $stored_events )
						);
						?>
					</p>
				<?php else : ?>
					<p><?php esc_html_e( 'Detailed game events will be rendered here by future frontend templates.', 'mahl-manager' ); ?></p>
				<?php endif; ?>
			</section>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
