<?php
/**
 * Matches archive template.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$context = ML_Frontend_Router::get_selected_context();
$matches = ML_Cache::get_fixtures( (int) $context['season_id'], (int) $context['competition_id'] );

get_header();
?>
<div class="ml-league ml-league-matches">
	<h1><?php echo esc_html__( 'Matches', 'mahl-league' ); ?></h1>
	<?php if ( empty( $matches ) ) : ?>
		<p><?php echo esc_html__( 'Data not ready. Please recompute in admin.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<ul>
			<?php foreach ( $matches as $match ) : ?>
				<?php
				$home_name = isset( $match['home_name'] ) ? (string) $match['home_name'] : '';
				$away_name = isset( $match['away_name'] ) ? (string) $match['away_name'] : '';
				$url       = isset( $match['url'] ) ? (string) $match['url'] : '';
				$datetime  = isset( $match['datetime'] ) ? (string) $match['datetime'] : '';
				?>
				<li>
					<a href="<?php echo esc_url( $url ); ?>">
						<?php echo esc_html( $home_name . ' vs ' . $away_name ); ?>
					</a>
					<?php if ( '' !== $datetime ) : ?>
						- <?php echo esc_html( $datetime ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php
get_footer();
