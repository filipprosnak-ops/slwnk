<?php
/**
 * Team single template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_query_var( 'mahl_template_data', array() );

get_header();
?>
<main id="primary" class="site-main mahl-single mahl-single-team">
	<article <?php post_class(); ?>>
		<header class="entry-header">
			<h1 class="entry-title"><?php echo esc_html( $data['title'] ); ?></h1>
		</header>

		<?php if ( ! empty( $data['excerpt'] ) ) : ?>
			<div class="entry-summary"><?php echo wp_kses_post( wpautop( $data['excerpt'] ) ); ?></div>
		<?php endif; ?>

		<section class="mahl-team-info">
			<h2><?php esc_html_e( 'Team Information', 'mahl-manager' ); ?></h2>
			<?php if ( ! empty( $data['season'] ) ) : ?>
				<p>
					<?php esc_html_e( 'Season:', 'mahl-manager' ); ?>
					<a href="<?php echo esc_url( $data['season']['permalink'] ); ?>"><?php echo esc_html( $data['season']['title'] ); ?></a>
				</p>
			<?php endif; ?>
			<?php if ( ! empty( $data['content'] ) ) : ?>
				<div class="entry-content"><?php echo wp_kses_post( $data['content'] ); ?></div>
			<?php else : ?>
				<p><?php esc_html_e( 'No additional team information is available yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-team-roster">
			<h2><?php esc_html_e( 'Player Roster', 'mahl-manager' ); ?></h2>
			<?php if ( ! empty( $data['roster'] ) ) : ?>
				<ul class="mahl-entry-list">
					<?php foreach ( $data['roster'] as $player ) : ?>
						<li><a href="<?php echo esc_url( $player['permalink'] ); ?>"><?php echo esc_html( $player['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'No players are assigned to this team yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-team-games">
			<h2><?php esc_html_e( 'Games', 'mahl-manager' ); ?></h2>
			<?php if ( ! empty( $data['games'] ) ) : ?>
				<ul class="mahl-entry-list">
					<?php foreach ( $data['games'] as $game ) : ?>
						<li>
							<a href="<?php echo esc_url( $game['permalink'] ); ?>"><?php echo esc_html( $game['title'] ); ?></a>
							<?php if ( ! empty( $game['home_team'] ) || ! empty( $game['away_team'] ) ) : ?>
								- <?php echo esc_html( ! empty( $game['home_team'] ) ? $game['home_team']['title'] : __( 'Home team TBD', 'mahl-manager' ) ); ?>
								<?php esc_html_e( 'vs', 'mahl-manager' ); ?>
								<?php echo esc_html( ! empty( $game['away_team'] ) ? $game['away_team']['title'] : __( 'Away team TBD', 'mahl-manager' ) ); ?>
							<?php endif; ?>
							<?php if ( ! empty( $game['match_date'] ) ) : ?>
								- <?php echo esc_html( $game['match_date'] ); ?>
							<?php endif; ?>
							<?php if ( ! empty( $game['score_text'] ) ) : ?>
								- <?php echo esc_html( $game['score_text'] ); ?>
							<?php endif; ?>
							<?php if ( ! empty( $game['status_label'] ) ) : ?>
								- <?php echo esc_html( $game['status_label'] ); ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'No games are linked to this team yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-team-standings-context">
			<h2><?php esc_html_e( 'Standings Context', 'mahl-manager' ); ?></h2>
			<?php if ( ! empty( $data['standings_context']['season'] ) ) : ?>
				<p>
					<?php
					printf(
						/* translators: 1: position, 2: points. */
						esc_html__( 'Overall season position: %1$d, points: %2$d', 'mahl-manager' ),
						absint( $data['standings_context']['season']['position'] ),
						absint( $data['standings_context']['season']['points'] )
					);
					?>
				</p>
			<?php else : ?>
				<p><?php esc_html_e( 'No standings context is available yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $data['standings_context']['phases'] ) ) : ?>
				<ul class="mahl-entry-list">
					<?php foreach ( $data['standings_context']['phases'] as $phase_context ) : ?>
						<li>
							<?php if ( ! empty( $phase_context['phase']['permalink'] ) ) : ?>
								<a href="<?php echo esc_url( $phase_context['phase']['permalink'] ); ?>"><?php echo esc_html( $phase_context['phase']['title'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $phase_context['phase']['title'] ); ?>
							<?php endif; ?>
							<?php
							printf(
								' - %s',
								esc_html(
									sprintf(
										/* translators: 1: position, 2: points. */
										__( 'position %1$d, points %2$d', 'mahl-manager' ),
										absint( $phase_context['row']['position'] ),
										absint( $phase_context['row']['points'] )
									)
								)
							);
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
	</article>
</main>
<?php
get_footer();
