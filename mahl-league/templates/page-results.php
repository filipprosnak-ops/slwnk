<?php
/**
 * Results page template.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="ml-league ml-league-results">
	<h1><?php echo esc_html__( 'Results', 'mahl-league' ); ?></h1>
	<?php
	$results = get_posts(
		array(
			'post_type'      => 'ml_match',
			'post_status'    => array( 'publish', 'future' ),
			'posts_per_page' => -1,
			'meta_key'       => 'ml_match_datetime',
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'   => 'ml_status',
					'value' => 'played',
				),
			),
		)
	);
	?>
	<?php if ( empty( $results ) ) : ?>
		<p><?php echo esc_html__( 'No played matches found.', 'mahl-league' ); ?></p>
	<?php else : ?>
		<ul>
			<?php foreach ( $results as $match ) : ?>
				<?php
				$home_team_id = absint( get_post_meta( $match->ID, 'ml_home_team_id', true ) );
				$away_team_id = absint( get_post_meta( $match->ID, 'ml_away_team_id', true ) );
				$home_name    = $home_team_id > 0 ? get_the_title( $home_team_id ) : __( 'TBD', 'mahl-league' );
				$away_name    = $away_team_id > 0 ? get_the_title( $away_team_id ) : __( 'TBD', 'mahl-league' );
				$home_score   = absint( get_post_meta( $match->ID, 'ml_home_score', true ) );
				$away_score   = absint( get_post_meta( $match->ID, 'ml_away_score', true ) );
				?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $match->ID ) ); ?>"><?php echo esc_html( $home_name . ' ' . $home_score . ' : ' . $away_score . ' ' . $away_name ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php
get_footer();
