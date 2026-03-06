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
	<header class="page-header mahl-page-header mahl-archive-header">
		<p class="mahl-page-header__eyebrow"><?php esc_html_e( 'Games', 'mahl-manager' ); ?></p>
		<h1 class="page-title mahl-page-title"><?php echo esc_html( $data['title'] ); ?></h1>
	</header>

	<?php if ( ! empty( $data['entries'] ) ) : ?>
		<div class="mahl-match-list mahl-match-list--archive">
			<?php foreach ( $data['entries'] as $entry ) : ?>
				<article class="mahl-match-card mahl-match-card--archive">
					<header class="mahl-match-card__header">
						<div class="mahl-match-card__datetime">
							<?php if ( ! empty( $entry['match_date'] ) ) : ?>
								<span class="mahl-match-card__date"><?php echo esc_html( $entry['match_date'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $entry['match_time'] ) ) : ?>
								<span class="mahl-match-card__time"><?php echo esc_html( $entry['match_time'] ); ?></span>
							<?php endif; ?>
						</div>
						<span class="mahl-match-card__status"><?php echo esc_html( $entry['status_label'] ); ?></span>
					</header>
					<div class="mahl-match-card__body">
						<h2 class="mahl-entry-title">
							<a href="<?php echo esc_url( $entry['permalink'] ); ?>"><?php echo esc_html( $entry['title'] ); ?></a>
						</h2>
						<div class="mahl-match-card__teams">
							<div class="mahl-match-card__team mahl-match-card__team--home"><?php echo esc_html( ! empty( $entry['home_team'] ) ? $entry['home_team']['title'] : __( 'Home team TBD', 'mahl-manager' ) ); ?></div>
							<div class="mahl-match-card__score"><?php echo esc_html( $entry['score']['display_state'] ); ?></div>
							<div class="mahl-match-card__team mahl-match-card__team--away"><?php echo esc_html( ! empty( $entry['away_team'] ) ? $entry['away_team']['title'] : __( 'Away team TBD', 'mahl-manager' ) ); ?></div>
						</div>
						<div class="mahl-match-card__meta">
							<?php if ( ! empty( $entry['phase'] ) ) : ?>
								<span class="mahl-match-card__phase"><a href="<?php echo esc_url( $entry['phase']['permalink'] ); ?>"><?php echo esc_html( $entry['phase']['title'] ); ?></a></span>
							<?php endif; ?>
							<?php if ( ! empty( $entry['venue'] ) ) : ?>
								<span class="mahl-match-card__venue"><?php echo esc_html( $entry['venue'] ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No games found.', 'mahl-manager' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
