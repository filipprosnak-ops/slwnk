<?php
/**
 * Teams archive template.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$teams = ML_Cache::get_teams_view();

get_header();
?>
<div class="ml-league ml-league-teams">
	<h1><?php echo esc_html__( 'Teams', 'mahl-league' ); ?></h1>
	<?php if ( empty( $teams ) ) : ?>
		<p><?php echo esc_html__( 'Data not ready. Please recompute in admin.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<ul>
			<?php foreach ( $teams as $team ) : ?>
				<?php
				$title = isset( $team['title'] ) ? (string) $team['title'] : '';
				$url   = isset( $team['url'] ) ? (string) $team['url'] : '';
				?>
				<li>
					<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php
get_footer();
