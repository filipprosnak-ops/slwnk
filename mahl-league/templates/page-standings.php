<?php
/**
 * Standings page template.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$context   = ML_Frontend_Router::get_selected_context();
$standings = ML_Cache::get_standings( $context['season_id'], $context['competition_id'] );

get_header();
?>
<div class="ml-league ml-league-standings">
	<h1><?php echo esc_html__( 'Standings', 'mahl-league' ); ?></h1>
	<?php if ( empty( $standings ) ) : ?>
		<p><?php echo esc_html__( 'No cached standings available.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<table>
			<thead>
			<tr>
				<th><?php echo esc_html__( '#', 'mahl-league' ); ?></th>
				<th><?php echo esc_html__( 'Team', 'mahl-league' ); ?></th>
				<th><?php echo esc_html__( 'P', 'mahl-league' ); ?></th>
				<th><?php echo esc_html__( 'W', 'mahl-league' ); ?></th>
				<th><?php echo esc_html__( 'D', 'mahl-league' ); ?></th>
				<th><?php echo esc_html__( 'L', 'mahl-league' ); ?></th>
				<th><?php echo esc_html__( 'GF', 'mahl-league' ); ?></th>
				<th><?php echo esc_html__( 'GA', 'mahl-league' ); ?></th>
				<th><?php echo esc_html__( 'GD', 'mahl-league' ); ?></th>
				<th><?php echo esc_html__( 'PTS', 'mahl-league' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $standings as $row ) : ?>
				<?php
				$position  = isset( $row['position'] ) ? absint( $row['position'] ) : 0;
				$team_name = isset( $row['team_name'] ) ? (string) $row['team_name'] : '';
				$played    = isset( $row['played'] ) ? absint( $row['played'] ) : 0;
				$wins      = isset( $row['wins'] ) ? absint( $row['wins'] ) : 0;
				$draws     = isset( $row['draws'] ) ? absint( $row['draws'] ) : 0;
				$losses    = isset( $row['losses'] ) ? absint( $row['losses'] ) : 0;
				$gf        = isset( $row['gf'] ) ? absint( $row['gf'] ) : 0;
				$ga        = isset( $row['ga'] ) ? absint( $row['ga'] ) : 0;
				$gd        = isset( $row['gd'] ) ? (int) $row['gd'] : 0;
				$points    = isset( $row['points'] ) ? absint( $row['points'] ) : 0;
				?>
				<tr>
					<td><?php echo esc_html( (string) $position ); ?></td>
					<td><?php echo esc_html( $team_name ); ?></td>
					<td><?php echo esc_html( (string) $played ); ?></td>
					<td><?php echo esc_html( (string) $wins ); ?></td>
					<td><?php echo esc_html( (string) $draws ); ?></td>
					<td><?php echo esc_html( (string) $losses ); ?></td>
					<td><?php echo esc_html( (string) $gf ); ?></td>
					<td><?php echo esc_html( (string) $ga ); ?></td>
					<td><?php echo esc_html( (string) $gd ); ?></td>
					<td><?php echo esc_html( (string) $points ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
<?php
get_footer();
