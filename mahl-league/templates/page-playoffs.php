<?php
/**
 * Playoffs page template.
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
<div class="ml-league ml-league-playoffs">
	<h1><?php echo esc_html__( 'Playoffs', 'mahl-league' ); ?></h1>
	<?php if ( empty( $standings ) ) : ?>
		<p><?php echo esc_html__( 'No cached data available for playoffs view.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<p><?php echo esc_html__( 'Cached standings snapshot used for playoffs context.', 'mahl-league' ); ?></p>
		<ol>
			<?php foreach ( $standings as $row ) : ?>
				<?php
				$team_name = isset( $row['team_name'] ) ? (string) $row['team_name'] : '';
				$points    = isset( $row['points'] ) ? absint( $row['points'] ) : 0;
				?>
				<li><?php echo esc_html( $team_name ); ?> (<?php echo esc_html( (string) $points ); ?>)</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</div>
<?php
get_footer();
