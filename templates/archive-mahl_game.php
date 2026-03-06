<?php
/**
 * Game archive template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_query_var( 'mahl_template_data', array() );

get_header();
?>
<main id="primary" class="site-main mahl-archive mahl-archive-game">
	<header class="page-header">
		<h1 class="page-title"><?php echo esc_html( $data['title'] ); ?></h1>
	</header>

	<?php if ( ! empty( $data['entries'] ) ) : ?>
		<ul class="mahl-entry-list">
			<?php foreach ( $data['entries'] as $entry ) : ?>
				<li class="mahl-entry-item">
					<h2 class="mahl-entry-title">
						<a href="<?php echo esc_url( $entry['permalink'] ); ?>"><?php echo esc_html( $entry['title'] ); ?></a>
					</h2>
					<p class="mahl-entry-meta">
						<?php
						echo esc_html( ! empty( $entry['home_team'] ) ? $entry['home_team']['title'] : __( 'Home team TBD', 'mahl-manager' ) );
						echo ' vs ';
						echo esc_html( ! empty( $entry['away_team'] ) ? $entry['away_team']['title'] : __( 'Away team TBD', 'mahl-manager' ) );
						?>
					</p>
					<?php if ( ! empty( $entry['season'] ) ) : ?>
						<p class="mahl-entry-meta">
							<?php esc_html_e( 'Season:', 'mahl-manager' ); ?>
							<a href="<?php echo esc_url( $entry['season']['permalink'] ); ?>"><?php echo esc_html( $entry['season']['title'] ); ?></a>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $entry['phase'] ) ) : ?>
						<p class="mahl-entry-meta">
							<?php esc_html_e( 'Phase:', 'mahl-manager' ); ?>
							<a href="<?php echo esc_url( $entry['phase']['permalink'] ); ?>"><?php echo esc_html( $entry['phase']['title'] ); ?></a>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $entry['match_date'] ) || ! empty( $entry['match_time'] ) ) : ?>
						<p class="mahl-entry-meta">
							<?php echo esc_html( trim( $entry['match_date'] . ' ' . $entry['match_time'] ) ); ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $entry['score_text'] ) ) : ?>
						<p class="mahl-entry-meta">
							<?php esc_html_e( 'Score:', 'mahl-manager' ); ?>
							<?php echo esc_html( $entry['score_text'] ); ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $entry['status_label'] ) ) : ?>
						<p class="mahl-entry-meta">
							<?php esc_html_e( 'Status:', 'mahl-manager' ); ?>
							<?php echo esc_html( $entry['status_label'] ); ?>
						</p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No games found.', 'mahl-manager' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
