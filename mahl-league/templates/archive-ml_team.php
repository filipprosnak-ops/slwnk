<?php
/**
 * Teams archive template.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="ml-league ml-league-teams">
	<h1><?php echo esc_html__( 'Teams', 'mahl-league' ); ?></h1>
	<?php
	$teams = get_posts(
		array(
			'post_type'      => 'ml_team',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	?>
	<?php if ( empty( $teams ) ) : ?>
		<p><?php echo esc_html__( 'No teams found.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<ul>
			<?php foreach ( $teams as $team ) : ?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $team->ID ) ); ?>"><?php echo esc_html( $team->post_title ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php
get_footer();
