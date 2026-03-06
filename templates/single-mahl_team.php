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
	<article <?php post_class( 'mahl-layout mahl-layout-team' ); ?>>
		<header class="entry-header mahl-page-header mahl-team-header">
			<div class="mahl-page-header__main">
				<p class="mahl-page-header__eyebrow"><?php esc_html_e( 'Team', 'mahl-manager' ); ?></p>
				<h1 class="entry-title mahl-page-title"><?php echo esc_html( $data['title'] ); ?></h1>
			</div>
			<?php if ( ! empty( $data['season'] ) ) : ?>
				<div class="mahl-page-header__context">
					<a class="mahl-context-link" href="<?php echo esc_url( $data['season']['permalink'] ); ?>"><?php echo esc_html( $data['season']['title'] ); ?></a>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( ! empty( $data['excerpt'] ) ) : ?>
			<div class="entry-summary mahl-page-summary"><?php echo wp_kses_post( wpautop( $data['excerpt'] ) ); ?></div>
		<?php endif; ?>

		<section class="mahl-section mahl-team-overview" aria-labelledby="mahl-team-overview-title">
			<header class="mahl-section__header">
				<h2 id="mahl-team-overview-title" class="mahl-section__title"><?php esc_html_e( 'Team Overview', 'mahl-manager' ); ?></h2>
			</header>
			<ul class="mahl-summary-list mahl-summary-list--team">
				<?php if ( ! empty( $data['season'] ) ) : ?>
					<li class="mahl-summary-list__item">
						<?php esc_html_e( 'Season:', 'mahl-manager' ); ?>
						<a href="<?php echo esc_url( $data['season']['permalink'] ); ?>"><?php echo esc_html( $data['season']['title'] ); ?></a>
					</li>
				<?php endif; ?>
				<li class="mahl-summary-list__item"><?php printf( esc_html__( 'Roster: %d players', 'mahl-manager' ), absint( $data['summary']['roster_count'] ) ); ?></li>
				<li class="mahl-summary-list__item"><?php printf( esc_html__( 'Games: %d', 'mahl-manager' ), absint( $data['summary']['game_count'] ) ); ?></li>
			</ul>
			<?php if ( ! empty( $data['content'] ) ) : ?>
				<div class="entry-content mahl-page-content"><?php echo wp_kses_post( $data['content'] ); ?></div>
			<?php else : ?>
				<p><?php esc_html_e( 'No additional team information is available yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-section mahl-team-standings-context" aria-labelledby="mahl-team-standings-title">
			<header class="mahl-section__header">
				<h2 id="mahl-team-standings-title" class="mahl-section__title"><?php esc_html_e( 'Standings Context', 'mahl-manager' ); ?></h2>
			</header>
			<?php if ( ! empty( $data['standings_context']['season'] ) ) : ?>
				<div class="mahl-standings-context__overall">
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
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No standings context is available yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $data['standings_context']['phases'] ) ) : ?>
				<ul class="mahl-entry-list mahl-phase-context-list">
					<?php foreach ( $data['standings_context']['phases'] as $phase_context ) : ?>
						<li class="mahl-phase-context-list__item">
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

		<section class="mahl-section mahl-team-roster" aria-labelledby="mahl-team-roster-title">
			<header class="mahl-section__header">
				<h2 id="mahl-team-roster-title" class="mahl-section__title"><?php esc_html_e( 'Roster', 'mahl-manager' ); ?></h2>
			</header>
			<?php if ( ! empty( $data['roster'] ) ) : ?>
				<ul class="mahl-entry-list mahl-roster-list">
					<?php foreach ( $data['roster'] as $player ) : ?>
						<li class="mahl-roster-list__item"><a href="<?php echo esc_url( $player['permalink'] ); ?>"><?php echo esc_html( $player['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'No players are assigned to this team yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<?php if ( ! empty( $data['phase_navigation'] ) ) : ?>
			<nav class="mahl-phase-nav" aria-label="<?php esc_attr_e( 'Team games by phase', 'mahl-manager' ); ?>">
				<ul class="mahl-phase-nav__list">
					<?php foreach ( $data['phase_navigation'] as $phase_nav ) : ?>
						<li class="mahl-phase-nav__item">
							<a class="mahl-phase-nav__link" href="#<?php echo esc_attr( $phase_nav['id'] ); ?>">
								<span class="mahl-phase-nav__title"><?php echo esc_html( $phase_nav['title'] ); ?></span>
								<span class="mahl-phase-nav__meta">
									<?php
									printf(
										/* translators: 1: game count, 2: round count. */
										esc_html__( '%1$d games / %2$d rounds', 'mahl-manager' ),
										absint( $phase_nav['game_count'] ),
										absint( $phase_nav['round_count'] )
									);
									?>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<section class="mahl-section mahl-team-games" aria-labelledby="mahl-team-games-title">
			<header class="mahl-section__header">
				<h2 id="mahl-team-games-title" class="mahl-section__title"><?php esc_html_e( 'Games', 'mahl-manager' ); ?></h2>
			</header>
			<?php if ( ! empty( $data['schedule_sections'] ) ) : ?>
				<?php foreach ( $data['schedule_sections'] as $section ) : ?>
					<section class="mahl-schedule-phase" id="<?php echo esc_attr( $section['id'] ); ?>">
						<header class="mahl-schedule-phase__header">
							<h3 class="mahl-schedule-phase__title"><?php echo esc_html( $section['phase']['title'] ); ?></h3>
							<ul class="mahl-schedule-phase__meta">
								<li><?php printf( esc_html__( '%d games', 'mahl-manager' ), absint( $section['summary']['game_count'] ) ); ?></li>
								<li><?php printf( esc_html__( '%d rounds', 'mahl-manager' ), absint( $section['summary']['round_count'] ) ); ?></li>
							</ul>
						</header>
						<div class="mahl-schedule-phase__rounds">
							<?php foreach ( $section['rounds'] as $round ) : ?>
								<section class="mahl-schedule-round">
									<h4 class="mahl-schedule-round__title"><?php echo esc_html( $round['title'] ); ?></h4>
									<div class="mahl-match-list">
										<?php foreach ( $round['games'] as $game ) : ?>
											<article class="mahl-match-card">
												<header class="mahl-match-card__header">
													<div class="mahl-match-card__datetime">
														<?php if ( ! empty( $game['match_date'] ) ) : ?>
															<span class="mahl-match-card__date"><?php echo esc_html( $game['match_date'] ); ?></span>
														<?php endif; ?>
														<?php if ( ! empty( $game['match_time'] ) ) : ?>
															<span class="mahl-match-card__time"><?php echo esc_html( $game['match_time'] ); ?></span>
														<?php endif; ?>
													</div>
													<span class="mahl-match-card__status"><?php echo esc_html( $game['status_label'] ); ?></span>
												</header>
												<div class="mahl-match-card__body">
													<div class="mahl-match-card__teams">
														<div class="mahl-match-card__team mahl-match-card__team--home"><?php echo esc_html( ! empty( $game['home_team'] ) ? $game['home_team']['title'] : __( 'Home team TBD', 'mahl-manager' ) ); ?></div>
														<div class="mahl-match-card__score"><?php echo esc_html( $game['score']['display_state'] ); ?></div>
														<div class="mahl-match-card__team mahl-match-card__team--away"><?php echo esc_html( ! empty( $game['away_team'] ) ? $game['away_team']['title'] : __( 'Away team TBD', 'mahl-manager' ) ); ?></div>
													</div>
													<div class="mahl-match-card__meta">
														<?php if ( ! empty( $game['venue'] ) ) : ?>
															<span class="mahl-match-card__venue"><?php echo esc_html( $game['venue'] ); ?></span>
														<?php endif; ?>
														<?php if ( ! empty( $game['group_label'] ) ) : ?>
															<span class="mahl-match-card__group"><?php echo esc_html( $game['group_label'] ); ?></span>
														<?php endif; ?>
													</div>
												</div>
												<footer class="mahl-match-card__footer">
													<a class="mahl-match-card__link" href="<?php echo esc_url( $game['permalink'] ); ?>"><?php esc_html_e( 'View Game', 'mahl-manager' ); ?></a>
												</footer>
											</article>
										<?php endforeach; ?>
									</div>
								</section>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No games are linked to this team yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>
	</article>
</main>
<?php
get_footer();
