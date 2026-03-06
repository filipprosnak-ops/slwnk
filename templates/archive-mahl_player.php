<?php
/**
 * Player archive template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_query_var( 'mahl_template_data', array() );

get_header();
?>
<main id="primary" class="site-main mahl-archive mahl-archive-player">
	<header class="page-header mahl-page-header mahl-archive-header">
		<p class="mahl-page-header__eyebrow"><?php esc_html_e( 'Players', 'mahl-manager' ); ?></p>
		<h1 class="page-title mahl-page-title"><?php echo esc_html( $data['title'] ); ?></h1>
	</header>

	<?php if ( ! empty( $data['entries'] ) ) : ?>
		<ul class="mahl-entry-list mahl-entry-list--cards">
			<?php foreach ( $data['entries'] as $entry ) : ?>
				<li class="mahl-entry-item mahl-entry-item--card">
					<article class="mahl-entry-card">
						<h2 class="mahl-entry-title">
							<a href="<?php echo esc_url( $entry['permalink'] ); ?>"><?php echo esc_html( $entry['title'] ); ?></a>
						</h2>
						<?php if ( ! empty( $entry['team'] ) ) : ?>
							<p class="mahl-entry-meta">
								<?php esc_html_e( 'Team:', 'mahl-manager' ); ?>
								<a href="<?php echo esc_url( $entry['team']['permalink'] ); ?>"><?php echo esc_html( $entry['team']['title'] ); ?></a>
							</p>
						<?php endif; ?>
						<?php if ( ! empty( $entry['season'] ) ) : ?>
							<p class="mahl-entry-meta">
								<?php esc_html_e( 'Season:', 'mahl-manager' ); ?>
								<a href="<?php echo esc_url( $entry['season']['permalink'] ); ?>"><?php echo esc_html( $entry['season']['title'] ); ?></a>
							</p>
						<?php endif; ?>
						<?php if ( ! empty( $entry['excerpt'] ) ) : ?>
							<div class="mahl-entry-summary"><?php echo wp_kses_post( wpautop( $entry['excerpt'] ) ); ?></div>
						<?php endif; ?>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No players found.', 'mahl-manager' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
