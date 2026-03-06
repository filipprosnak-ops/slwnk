<?php
/**
 * Season archive template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_query_var( 'mahl_template_data', array() );

get_header();
?>
<main id="primary" class="site-main mahl-archive mahl-archive-season">
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
					<?php if ( ! empty( $entry['excerpt'] ) ) : ?>
						<div class="mahl-entry-summary"><?php echo wp_kses_post( wpautop( $entry['excerpt'] ) ); ?></div>
					<?php endif; ?>
					<p class="mahl-entry-meta">
						<?php
						printf(
							/* translators: 1: phase count, 2: game count. */
							esc_html__( '%1$d phases, %2$d games', 'mahl-manager' ),
							absint( $entry['phase_count'] ),
							absint( $entry['game_count'] )
						);
						?>
					</p>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No seasons found.', 'mahl-manager' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
