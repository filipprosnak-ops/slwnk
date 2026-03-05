<?php
/**
 * Results page template.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$context = ML_Frontend_Router::get_selected_context();
$results = ML_Cache::get_results( (int) $context['season_id'], (int) $context['competition_id'] );

get_header();
?>
<div class="ml-league ml-league-results">
	<h1><?php echo esc_html__( 'Results', 'mahl-league' ); ?></h1>
	<?php if ( empty( $results ) ) : ?>
		<p><?php echo esc_html__( 'Data not ready. Please recompute in admin.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<ul>
			<?php foreach ( $results as $match ) : ?>
				<?php
				$home_name  = isset( $match['home_name'] ) ? (string) $match['home_name'] : '';
				$away_name  = isset( $match['away_name'] ) ? (string) $match['away_name'] : '';
				$home_score = isset( $match['home_score'] ) ? absint( $match['home_score'] ) : 0;
				$away_score = isset( $match['away_score'] ) ? absint( $match['away_score'] ) : 0;
				$url        = isset( $match['url'] ) ? (string) $match['url'] : '';
				?>
				<li>
					<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $home_name . ' ' . $home_score . ' : ' . $away_score . ' ' . $away_name ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php
get_footer();
