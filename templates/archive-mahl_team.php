<?php
/**
 * Team archive template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_query_var( 'mahl_template_data', array() );

get_header();
?>
<main id="primary" class="site-main mahl-archive mahl-archive-team">
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
					<?php if ( ! empty( $entry['season'] ) ) : ?>
						<p class="mahl-entry-meta">
							<?php esc_html_e( 'Season:', 'mahl-manager' ); ?>
							<a href="<?php echo esc_url( $entry['season']['permalink'] ); ?>"><?php echo esc_html( $entry['season']['title'] ); ?></a>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $entry['excerpt'] ) ) : ?>
						<div class="mahl-entry-summary"><?php echo wp_kses_post( wpautop( $entry['excerpt'] ) ); ?></div>
					<?php endif; ?>
					<p class="mahl-entry-meta">
						<?php
						printf(
							/* translators: %d: roster count. */
							esc_html__( '%d roster players', 'mahl-manager' ),
							absint( $entry['roster_count'] )
						);
						?>
					</p>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No teams found.', 'mahl-manager' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
