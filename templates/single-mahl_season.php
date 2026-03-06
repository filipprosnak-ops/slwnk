<?php
/**
 * Season single template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_query_var( 'mahl_template_data', array() );

get_header();
?>
<main id="primary" class="site-main mahl-single mahl-single-season">
	<article <?php post_class( 'mahl-layout mahl-layout-season' ); ?>>
		<header class="entry-header mahl-page-header mahl-season-header">
			<div class="mahl-page-header__main">
				<p class="mahl-page-header__eyebrow"><?php esc_html_e( 'Competition Season', 'mahl-manager' ); ?></p>
				<h1 class="entry-title mahl-page-title"><?php echo esc_html( $data['title'] ); ?></h1>
			</div>
		</header>

		<?php if ( ! empty( $data['excerpt'] ) ) : ?>
			<div class="entry-summary mahl-page-summary"><?php echo wp_kses_post( wpautop( $data['excerpt'] ) ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $data['content'] ) ) : ?>
			<div class="entry-content mahl-page-content"><?php echo wp_kses_post( $data['content'] ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $data['phase_navigation'] ) ) : ?>
			<nav class="mahl-phase-nav" aria-label="<?php esc_attr_e( 'Season phases', 'mahl-manager' ); ?>">
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

		<section class="mahl-section mahl-season-overview" aria-labelledby="mahl-season-overview-title">
			<header class="mahl-section__header">
				<h2 id="mahl-season-overview-title" class="mahl-section__title"><?php esc_html_e( 'Season Overview', 'mahl-manager' ); ?></h2>
			</header>
			<ul class="mahl-summary-list mahl-summary-list--season">
				<li class="mahl-summary-list__item"><?php printf( esc_html__( 'Phases: %d', 'mahl-manager' ), absint( $data['structure_overview']['phase_count'] ) ); ?></li>
				<li class="mahl-summary-list__item"><?php printf( esc_html__( 'Teams: %d', 'mahl-manager' ), absint( $data['structure_overview']['team_count'] ) ); ?></li>
				<li class="mahl-summary-list__item"><?php printf( esc_html__( 'Games: %d', 'mahl-manager' ), absint( $data['structure_overview']['game_count'] ) ); ?></li>
				<?php if ( ! empty( $data['structure_overview']['available_groups'] ) ) : ?>
					<li class="mahl-summary-list__item"><?php echo esc_html( sprintf( __( 'Groups / Brackets: %s', 'mahl-manager' ), implode( ', ', $data['structure_overview']['available_groups'] ) ) ); ?></li>
				<?php endif; ?>
			</ul>
		</section>

		<section class="mahl-section mahl-season-phases" aria-labelledby="mahl-season-phases-title">
			<header class="mahl-section__header">
				<h2 id="mahl-season-phases-title" class="mahl-section__title"><?php esc_html_e( 'Phases', 'mahl-manager' ); ?></h2>
			</header>
			<?php if ( ! empty( $data['phases'] ) ) : ?>
				<ul class="mahl-entry-list mahl-phase-list">
					<?php foreach ( $data['phases'] as $phase ) : ?>
						<li class="mahl-phase-list__item">
							<a class="mahl-phase-list__link" href="<?php echo esc_url( $phase['permalink'] ); ?>"><?php echo esc_html( $phase['title'] ); ?></a>
							<span class="mahl-phase-list__meta">
								<?php
								printf(
									/* translators: %d: game count. */
									esc_html__( '%d games', 'mahl-manager' ),
									absint( $phase['game_count'] )
								);
								?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'No phases have been assigned to this season yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-section mahl-season-standings" aria-labelledby="mahl-season-standings-title">
			<header class="mahl-section__header">
				<h2 id="mahl-season-standings-title" class="mahl-section__title"><?php esc_html_e( 'Standings', 'mahl-manager' ); ?></h2>
			</header>
			<?php if ( ! empty( $data['standings_sections'] ) ) : ?>
				<?php foreach ( $data['standings_sections'] as $section ) : ?>
					<section class="mahl-standings-section" id="<?php echo esc_attr( $section['id'] ); ?>">
						<header class="mahl-standings-section__header">
							<h3 class="mahl-standings-section__title"><?php echo esc_html( $section['title'] ); ?></h3>
							<?php if ( ! empty( $section['description'] ) ) : ?>
								<p class="mahl-standings-section__description"><?php echo esc_html( $section['description'] ); ?></p>
							<?php endif; ?>
						</header>
						<div class="mahl-table-wrap">
							<table class="mahl-table mahl-table--standings">
							<thead>
								<tr>
									<th><?php esc_html_e( '#', 'mahl-manager' ); ?></th>
									<th><?php esc_html_e( 'Team', 'mahl-manager' ); ?></th>
									<th><?php esc_html_e( 'GP', 'mahl-manager' ); ?></th>
									<th><?php esc_html_e( 'W', 'mahl-manager' ); ?></th>
									<th><?php esc_html_e( 'D', 'mahl-manager' ); ?></th>
									<th><?php esc_html_e( 'L', 'mahl-manager' ); ?></th>
									<th><?php esc_html_e( 'GF', 'mahl-manager' ); ?></th>
									<th><?php esc_html_e( 'GA', 'mahl-manager' ); ?></th>
									<th><?php esc_html_e( 'GD', 'mahl-manager' ); ?></th>
									<th><?php esc_html_e( 'Pts', 'mahl-manager' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $section['rows'] as $row ) : ?>
									<tr class="mahl-table__row">
										<td><?php echo esc_html( $row['position'] ); ?></td>
										<td><a href="<?php echo esc_url( get_permalink( $row['team_id'] ) ); ?>"><?php echo esc_html( $row['team_name'] ); ?></a></td>
										<td><?php echo esc_html( $row['games_played'] ); ?></td>
										<td><?php echo esc_html( $row['wins'] ); ?></td>
										<td><?php echo esc_html( $row['draws'] ); ?></td>
										<td><?php echo esc_html( $row['losses'] ); ?></td>
										<td><?php echo esc_html( $row['goals_for'] ); ?></td>
										<td><?php echo esc_html( $row['goals_against'] ); ?></td>
										<td><?php echo esc_html( $row['goal_difference'] ); ?></td>
										<td><?php echo esc_html( $row['points'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
							</table>
						</div>
					</section>
				<?php endforeach; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No completed games are available for standings yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-section mahl-season-schedule" aria-labelledby="mahl-season-schedule-title">
			<header class="mahl-section__header">
				<h2 id="mahl-season-schedule-title" class="mahl-section__title"><?php esc_html_e( 'Schedule', 'mahl-manager' ); ?></h2>
			</header>
			<?php if ( ! empty( $data['schedule_sections'] ) ) : ?>
				<?php foreach ( $data['schedule_sections'] as $section ) : ?>
					<section class="mahl-schedule-phase" id="<?php echo esc_attr( $section['id'] ); ?>">
						<header class="mahl-schedule-phase__header">
							<h3 class="mahl-schedule-phase__title">
								<?php if ( ! empty( $section['phase']['permalink'] ) ) : ?>
									<a href="<?php echo esc_url( $section['phase']['permalink'] ); ?>"><?php echo esc_html( $section['phase']['title'] ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $section['phase']['title'] ); ?>
								<?php endif; ?>
							</h3>
							<ul class="mahl-schedule-phase__meta">
								<li><?php printf( esc_html__( '%d games', 'mahl-manager' ), absint( $section['summary']['game_count'] ) ); ?></li>
								<li><?php printf( esc_html__( '%d rounds', 'mahl-manager' ), absint( $section['summary']['round_count'] ) ); ?></li>
								<?php if ( ! empty( $section['summary']['group_labels'] ) ) : ?>
									<li><?php echo esc_html( implode( ', ', $section['summary']['group_labels'] ) ); ?></li>
								<?php endif; ?>
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
				<p><?php esc_html_e( 'No games are assigned to this season yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>
	</article>
</main>
<?php
get_footer();
