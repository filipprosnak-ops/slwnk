<?php
/**
 * Stats page template.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$context = ML_Frontend_Router::get_selected_context();
$stats   = ML_Cache::get_stats( $context['season_id'], $context['competition_id'] );

$team_totals   = isset( $stats['team_totals'] ) && is_array( $stats['team_totals'] ) ? $stats['team_totals'] : array();
$player_totals = isset( $stats['player_totals'] ) && is_array( $stats['player_totals'] ) ? $stats['player_totals'] : array();

get_header();
?>
<div class="ml-league ml-league-stats">
	<h1><?php echo esc_html__( 'Statistics', 'mahl-league' ); ?></h1>

	<h2><?php echo esc_html__( 'Team Totals', 'mahl-league' ); ?></h2>
	<?php if ( empty( $team_totals ) ) : ?>
		<p><?php echo esc_html__( 'No cached team totals available.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<ul>
			<?php foreach ( $team_totals as $row ) : ?>
				<?php
				$team_name     = isset( $row['team_name'] ) ? (string) $row['team_name'] : '';
				$matches_count = isset( $row['matches'] ) ? absint( $row['matches'] ) : 0;
				$goals_for     = isset( $row['goals_for'] ) ? absint( $row['goals_for'] ) : 0;
				$goals_against = isset( $row['goals_against'] ) ? absint( $row['goals_against'] ) : 0;
				$goal_diff     = isset( $row['goal_diff'] ) ? (int) $row['goal_diff'] : 0;
				?>
				<li>
					<?php echo esc_html( $team_name ); ?>:
					<?php echo esc_html__( 'Matches', 'mahl-league' ); ?> <?php echo esc_html( (string) $matches_count ); ?>,
					<?php echo esc_html__( 'GF', 'mahl-league' ); ?> <?php echo esc_html( (string) $goals_for ); ?>,
					<?php echo esc_html__( 'GA', 'mahl-league' ); ?> <?php echo esc_html( (string) $goals_against ); ?>,
					<?php echo esc_html__( 'GD', 'mahl-league' ); ?> <?php echo esc_html( (string) $goal_diff ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<h2><?php echo esc_html__( 'Player Totals', 'mahl-league' ); ?></h2>
	<?php if ( empty( $player_totals ) ) : ?>
		<p><?php echo esc_html__( 'No cached player totals available.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<ul>
			<?php foreach ( $player_totals as $row ) : ?>
				<?php
				$player_name = isset( $row['player_name'] ) ? (string) $row['player_name'] : '';
				$goals       = isset( $row['goals'] ) ? absint( $row['goals'] ) : 0;
				$assists     = isset( $row['assists'] ) ? absint( $row['assists'] ) : 0;
				$points      = isset( $row['points'] ) ? absint( $row['points'] ) : 0;
				?>
				<li>
					<?php echo esc_html( $player_name ); ?>:
					<?php echo esc_html__( 'G', 'mahl-league' ); ?> <?php echo esc_html( (string) $goals ); ?>,
					<?php echo esc_html__( 'A', 'mahl-league' ); ?> <?php echo esc_html( (string) $assists ); ?>,
					<?php echo esc_html__( 'PTS', 'mahl-league' ); ?> <?php echo esc_html( (string) $points ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php
get_footer();
