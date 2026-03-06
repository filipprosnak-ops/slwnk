<?php
/**
 * Player single template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_query_var( 'mahl_template_data', array() );

get_header();
?>
<main id="primary" class="site-main mahl-single mahl-single-player">
	<article <?php post_class( 'mahl-layout mahl-layout-player' ); ?>>
		<header class="entry-header mahl-page-header mahl-player-header">
			<div class="mahl-page-header__main">
				<p class="mahl-page-header__eyebrow"><?php esc_html_e( 'Player Profile', 'mahl-manager' ); ?></p>
				<h1 class="entry-title mahl-page-title"><?php echo esc_html( $data['title'] ); ?></h1>
			</div>
			<div class="mahl-page-header__context">
				<?php if ( ! empty( $data['context']['team'] ) ) : ?>
					<a class="mahl-context-link" href="<?php echo esc_url( $data['context']['team']['permalink'] ); ?>"><?php echo esc_html( $data['context']['team']['title'] ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $data['context']['season'] ) ) : ?>
					<a class="mahl-context-link" href="<?php echo esc_url( $data['context']['season']['permalink'] ); ?>"><?php echo esc_html( $data['context']['season']['title'] ); ?></a>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( ! empty( $data['excerpt'] ) ) : ?>
			<div class="entry-summary mahl-page-summary"><?php echo wp_kses_post( wpautop( $data['excerpt'] ) ); ?></div>
		<?php endif; ?>

		<section class="mahl-section mahl-player-identity" aria-labelledby="mahl-player-identity-title">
			<header class="mahl-section__header">
				<h2 id="mahl-player-identity-title" class="mahl-section__title"><?php esc_html_e( 'Player Identity', 'mahl-manager' ); ?></h2>
			</header>
			<?php if ( ! empty( $data['team'] ) ) : ?>
				<p>
					<?php esc_html_e( 'Team:', 'mahl-manager' ); ?>
					<a href="<?php echo esc_url( $data['team']['permalink'] ); ?>"><?php echo esc_html( $data['team']['title'] ); ?></a>
				</p>
			<?php endif; ?>
			<?php if ( ! empty( $data['season'] ) ) : ?>
				<p>
					<?php esc_html_e( 'Season:', 'mahl-manager' ); ?>
					<a href="<?php echo esc_url( $data['season']['permalink'] ); ?>"><?php echo esc_html( $data['season']['title'] ); ?></a>
				</p>
			<?php endif; ?>
			<?php if ( ! empty( $data['content'] ) ) : ?>
				<div class="entry-content mahl-page-content"><?php echo wp_kses_post( $data['content'] ); ?></div>
			<?php else : ?>
				<p><?php esc_html_e( 'No additional player information is available yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-section mahl-player-statistics" aria-labelledby="mahl-player-statistics-title">
			<header class="mahl-section__header">
				<h2 id="mahl-player-statistics-title" class="mahl-section__title"><?php esc_html_e( 'Statistics', 'mahl-manager' ); ?></h2>
			</header>
			<dl class="mahl-stat-grid">
				<div class="mahl-stat-grid__item">
					<dt class="mahl-stat-grid__label"><?php esc_html_e( 'Games Played', 'mahl-manager' ); ?></dt>
					<dd class="mahl-stat-grid__value"><?php echo esc_html( $data['statistics']['games_played'] ); ?></dd>
				</div>
				<div class="mahl-stat-grid__item">
					<dt class="mahl-stat-grid__label"><?php esc_html_e( 'Goals', 'mahl-manager' ); ?></dt>
					<dd class="mahl-stat-grid__value"><?php echo esc_html( $data['statistics']['goals'] ); ?></dd>
				</div>
				<div class="mahl-stat-grid__item">
					<dt class="mahl-stat-grid__label"><?php esc_html_e( 'Assists', 'mahl-manager' ); ?></dt>
					<dd class="mahl-stat-grid__value"><?php echo esc_html( $data['statistics']['assists'] ); ?></dd>
				</div>
				<div class="mahl-stat-grid__item">
					<dt class="mahl-stat-grid__label"><?php esc_html_e( 'Points', 'mahl-manager' ); ?></dt>
					<dd class="mahl-stat-grid__value"><?php echo esc_html( $data['statistics']['points'] ); ?></dd>
				</div>
				<div class="mahl-stat-grid__item">
					<dt class="mahl-stat-grid__label"><?php esc_html_e( 'Penalty Minutes', 'mahl-manager' ); ?></dt>
					<dd class="mahl-stat-grid__value"><?php echo esc_html( $data['statistics']['penalty_minutes'] ); ?></dd>
				</div>
			</dl>
		</section>
	</article>
</main>
<?php
get_footer();
