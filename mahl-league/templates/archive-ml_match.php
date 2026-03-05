<?php
/**
 * Matches archive template.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="ml-league ml-league-matches">
	<h1><?php echo esc_html__( 'Matches', 'mahl-league' ); ?></h1>
	<?php
	$matches = get_posts(
		array(
			'post_type'      => 'ml_match',
			'post_status'    => array( 'publish', 'future' ),
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'meta_key'       => 'ml_match_datetime',
			'order'          => 'ASC',
		)
	);
	?>
	<?php if ( empty( $matches ) ) : ?>
		<p><?php echo esc_html__( 'No matches found.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<ul>
			<?php foreach ( $matches as $match ) : ?>
				<?php
				$home_team_id = absint( get_post_meta( $match->ID, 'ml_home_team_id', true ) );
				$away_team_id = absint( get_post_meta( $match->ID, 'ml_away_team_id', true ) );
				$home_name    = $home_team_id > 0 ? get_the_title( $home_team_id ) : __( 'TBD', 'mahl-league' );
				$away_name    = $away_team_id > 0 ? get_the_title( $away_team_id ) : __( 'TBD', 'mahl-league' );
				$datetime     = (string) get_post_meta( $match->ID, 'ml_match_datetime', true );
				?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $match->ID ) ); ?>">
						<?php echo esc_html( $home_name . ' vs ' . $away_name ); ?>
					</a>
					<?php if ( ! empty( $datetime ) ) : ?>
						- <?php echo esc_html( $datetime ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php
get_footer();
