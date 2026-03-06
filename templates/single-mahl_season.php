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
	<article <?php post_class(); ?>>
		<header class="entry-header">
			<h1 class="entry-title"><?php echo esc_html( $data['title'] ); ?></h1>
		</header>

		<?php if ( ! empty( $data['excerpt'] ) ) : ?>
			<div class="entry-summary"><?php echo wp_kses_post( wpautop( $data['excerpt'] ) ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $data['content'] ) ) : ?>
			<div class="entry-content"><?php echo wp_kses_post( $data['content'] ); ?></div>
		<?php endif; ?>

		<section class="mahl-season-structure-overview">
			<h2><?php esc_html_e( 'Season Structure Overview', 'mahl-manager' ); ?></h2>
			<ul>
				<li><?php printf( esc_html__( 'Phases: %d', 'mahl-manager' ), absint( $data['structure_overview']['phase_count'] ) ); ?></li>
				<li><?php printf( esc_html__( 'Teams: %d', 'mahl-manager' ), absint( $data['structure_overview']['team_count'] ) ); ?></li>
				<li><?php printf( esc_html__( 'Games: %d', 'mahl-manager' ), absint( $data['structure_overview']['game_count'] ) ); ?></li>
				<?php if ( ! empty( $data['structure_overview']['available_groups'] ) ) : ?>
					<li><?php echo esc_html( sprintf( __( 'Groups / Brackets: %s', 'mahl-manager' ), implode( ', ', $data['structure_overview']['available_groups'] ) ) ); ?></li>
				<?php endif; ?>
			</ul>
		</section>

		<section class="mahl-season-phases">
			<h2><?php esc_html_e( 'Season Phases', 'mahl-manager' ); ?></h2>
			<?php if ( ! empty( $data['phases'] ) ) : ?>
				<ul class="mahl-entry-list">
					<?php foreach ( $data['phases'] as $phase ) : ?>
						<li>
							<a href="<?php echo esc_url( $phase['permalink'] ); ?>"><?php echo esc_html( $phase['title'] ); ?></a>
							<?php
							printf(
								' - %s',
								esc_html(
									sprintf(
										/* translators: %d: game count. */
										__( '%d games', 'mahl-manager' ),
										absint( $phase['game_count'] )
									)
								)
							);
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'No phases have been assigned to this season yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-season-standings">
			<h2><?php esc_html_e( 'Standings', 'mahl-manager' ); ?></h2>
			<?php if ( ! empty( $data['standings_sections'] ) ) : ?>
				<?php foreach ( $data['standings_sections'] as $section ) : ?>
					<section class="mahl-standings-section">
						<h3><?php echo esc_html( $section['title'] ); ?></h3>
						<table>
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
									<tr>
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
					</section>
				<?php endforeach; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No completed games are available for standings yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-season-games">
			<h2><?php esc_html_e( 'Games by Phase', 'mahl-manager' ); ?></h2>
			<?php if ( ! empty( $data['games_by_phase'] ) ) : ?>
				<?php foreach ( $data['games_by_phase'] as $group ) : ?>
					<section class="mahl-season-phase-games">
						<h3>
							<?php if ( ! empty( $group['phase']['permalink'] ) ) : ?>
								<a href="<?php echo esc_url( $group['phase']['permalink'] ); ?>"><?php echo esc_html( $group['phase']['title'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $group['phase']['title'] ); ?>
							<?php endif; ?>
						</h3>
						<ul class="mahl-entry-list">
							<?php foreach ( $group['games'] as $game ) : ?>
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
								</li>
							<?php endforeach; ?>
						</ul>
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
