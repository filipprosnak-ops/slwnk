<?php
/**
 * Game archive template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main mahl-archive mahl-archive-game">
	<header class="page-header">
		<h1 class="page-title"><?php post_type_archive_title(); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<ul class="mahl-entry-list">
			<?php
			while ( have_posts() ) :
				the_post();
				$home_team_id = absint( get_post_meta( get_the_ID(), '_mahl_home_team_id', true ) );
				$away_team_id = absint( get_post_meta( get_the_ID(), '_mahl_away_team_id', true ) );
				$status       = get_post_meta( get_the_ID(), '_mahl_status', true );
				?>
				<li class="mahl-entry-item">
					<h2 class="mahl-entry-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<p class="mahl-entry-meta">
						<?php
						echo esc_html( $home_team_id ? get_the_title( $home_team_id ) : __( 'Home team TBD', 'mahl-manager' ) );
						echo ' vs ';
						echo esc_html( $away_team_id ? get_the_title( $away_team_id ) : __( 'Away team TBD', 'mahl-manager' ) );
						?>
					</p>
					<?php if ( ! empty( $status ) ) : ?>
						<p class="mahl-entry-meta">
							<?php esc_html_e( 'Status:', 'mahl-manager' ); ?>
							<?php echo esc_html( ucwords( str_replace( '_', ' ', $status ) ) ); ?>
						</p>
					<?php endif; ?>
				</li>
			<?php endwhile; ?>
		</ul>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No games found.', 'mahl-manager' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
