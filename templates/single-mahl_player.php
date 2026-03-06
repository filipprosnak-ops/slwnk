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
	<article <?php post_class(); ?>>
		<header class="entry-header">
			<h1 class="entry-title"><?php echo esc_html( $data['title'] ); ?></h1>
		</header>

		<?php if ( ! empty( $data['excerpt'] ) ) : ?>
			<div class="entry-summary"><?php echo wp_kses_post( wpautop( $data['excerpt'] ) ); ?></div>
		<?php endif; ?>

		<section class="mahl-player-info">
			<h2><?php esc_html_e( 'Player Information', 'mahl-manager' ); ?></h2>
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
				<div class="entry-content"><?php echo wp_kses_post( $data['content'] ); ?></div>
			<?php else : ?>
				<p><?php esc_html_e( 'No additional player information is available yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="mahl-player-statistics">
			<h2><?php esc_html_e( 'Statistics', 'mahl-manager' ); ?></h2>
			<dl class="mahl-definition-list">
				<dt><?php esc_html_e( 'Games Played', 'mahl-manager' ); ?></dt>
				<dd><?php echo esc_html( $data['statistics']['games_played'] ); ?></dd>
				<dt><?php esc_html_e( 'Goals', 'mahl-manager' ); ?></dt>
				<dd><?php echo esc_html( $data['statistics']['goals'] ); ?></dd>
				<dt><?php esc_html_e( 'Assists', 'mahl-manager' ); ?></dt>
				<dd><?php echo esc_html( $data['statistics']['assists'] ); ?></dd>
				<dt><?php esc_html_e( 'Points', 'mahl-manager' ); ?></dt>
				<dd><?php echo esc_html( $data['statistics']['points'] ); ?></dd>
				<dt><?php esc_html_e( 'Penalty Minutes', 'mahl-manager' ); ?></dt>
				<dd><?php echo esc_html( $data['statistics']['penalty_minutes'] ); ?></dd>
			</dl>
		</section>
	</article>
</main>
<?php
get_footer();
