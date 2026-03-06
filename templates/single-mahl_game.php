<?php
/**
 * Game single template.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_query_var( 'mahl_template_data', array() );

get_header();
?>
<main id="primary" class="site-main mahl-single mahl-single-game">
	<article <?php post_class(); ?>>
		<header class="entry-header">
			<h1 class="entry-title"><?php echo esc_html( $data['title'] ); ?></h1>
		</header>

		<?php if ( ! empty( $data['excerpt'] ) ) : ?>
			<div class="entry-summary"><?php echo wp_kses_post( wpautop( $data['excerpt'] ) ); ?></div>
		<?php endif; ?>

		<section class="mahl-game-summary">
			<h2><?php esc_html_e( 'Game Summary', 'mahl-manager' ); ?></h2>
			<dl class="mahl-definition-list">
				<?php if ( ! empty( $data['season'] ) ) : ?>
					<dt><?php esc_html_e( 'Season', 'mahl-manager' ); ?></dt>
					<dd><a href="<?php echo esc_url( $data['season']['permalink'] ); ?>"><?php echo esc_html( $data['season']['title'] ); ?></a></dd>
				<?php endif; ?>

				<?php if ( ! empty( $data['phase'] ) ) : ?>
					<dt><?php esc_html_e( 'Phase', 'mahl-manager' ); ?></dt>
					<dd><a href="<?php echo esc_url( $data['phase']['permalink'] ); ?>"><?php echo esc_html( $data['phase']['title'] ); ?></a></dd>
				<?php endif; ?>

				<?php if ( ! empty( $data['round_number'] ) ) : ?>
					<dt><?php esc_html_e( 'Round', 'mahl-manager' ); ?></dt>
					<dd><?php echo esc_html( $data['round_number'] ); ?></dd>
				<?php endif; ?>

				<?php if ( ! empty( $data['group_key'] ) ) : ?>
					<dt><?php esc_html_e( 'Group / Bracket', 'mahl-manager' ); ?></dt>
					<dd><?php echo esc_html( ucwords( str_replace( array( '-', '_' ), ' ', $data['group_key'] ) ) ); ?></dd>
				<?php endif; ?>

				<?php if ( ! empty( $data['venue'] ) ) : ?>
					<dt><?php esc_html_e( 'Venue', 'mahl-manager' ); ?></dt>
					<dd><?php echo esc_html( $data['venue'] ); ?></dd>
				<?php endif; ?>

				<?php if ( ! empty( $data['match_date'] ) ) : ?>
					<dt><?php esc_html_e( 'Date', 'mahl-manager' ); ?></dt>
					<dd><?php echo esc_html( $data['match_date'] ); ?></dd>
				<?php endif; ?>

				<?php if ( ! empty( $data['match_time'] ) ) : ?>
					<dt><?php esc_html_e( 'Time', 'mahl-manager' ); ?></dt>
					<dd><?php echo esc_html( $data['match_time'] ); ?></dd>
				<?php endif; ?>

				<?php if ( ! empty( $data['status_label'] ) ) : ?>
					<dt><?php esc_html_e( 'Status', 'mahl-manager' ); ?></dt>
					<dd><?php echo esc_html( $data['status_label'] ); ?></dd>
				<?php endif; ?>

				<dt><?php esc_html_e( 'Home Team', 'mahl-manager' ); ?></dt>
				<dd>
					<?php if ( ! empty( $data['home_team'] ) ) : ?>
						<a href="<?php echo esc_url( $data['home_team']['permalink'] ); ?>"><?php echo esc_html( $data['home_team']['title'] ); ?></a>
					<?php else : ?>
						<?php esc_html_e( 'Not assigned', 'mahl-manager' ); ?>
					<?php endif; ?>
				</dd>

				<dt><?php esc_html_e( 'Away Team', 'mahl-manager' ); ?></dt>
				<dd>
					<?php if ( ! empty( $data['away_team'] ) ) : ?>
						<a href="<?php echo esc_url( $data['away_team']['permalink'] ); ?>"><?php echo esc_html( $data['away_team']['title'] ); ?></a>
					<?php else : ?>
						<?php esc_html_e( 'Not assigned', 'mahl-manager' ); ?>
					<?php endif; ?>
				</dd>

				<dt><?php esc_html_e( 'Final Score', 'mahl-manager' ); ?></dt>
				<dd>
					<?php if ( ! empty( $data['final_score'] ) ) : ?>
						<?php echo esc_html( $data['final_score'] ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Not available', 'mahl-manager' ); ?>
					<?php endif; ?>
				</dd>
			</dl>
		</section>

		<?php if ( ! empty( $data['period_scores'] ) ) : ?>
			<section class="mahl-game-period-scores">
				<h2><?php esc_html_e( 'Period Scores', 'mahl-manager' ); ?></h2>
				<ul class="mahl-entry-list">
					<?php foreach ( $data['period_scores'] as $score_row ) : ?>
						<li>
							<?php echo esc_html( $score_row['label'] ); ?>:
							<?php echo esc_html( ( null !== $score_row['home_score'] ? $score_row['home_score'] : '-' ) . ' : ' . ( null !== $score_row['away_score'] ? $score_row['away_score'] : '-' ) ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $data['content'] ) ) : ?>
			<div class="entry-content"><?php echo wp_kses_post( $data['content'] ); ?></div>
		<?php endif; ?>

		<section class="mahl-game-events">
			<h2><?php esc_html_e( 'Game Events', 'mahl-manager' ); ?></h2>
			<?php if ( ! empty( $data['events'] ) ) : ?>
				<ul class="mahl-entry-list">
					<?php foreach ( $data['events'] as $event ) : ?>
						<li>
							<strong><?php echo esc_html( $event['event_type_label'] ); ?></strong>
							<?php if ( ! empty( $event['team'] ) ) : ?>
								- <a href="<?php echo esc_url( $event['team']['permalink'] ); ?>"><?php echo esc_html( $event['team']['title'] ); ?></a>
							<?php endif; ?>
							<?php if ( ! empty( $event['period_number'] ) ) : ?>
								- <?php echo esc_html( sprintf( __( 'Period %d', 'mahl-manager' ), $event['period_number'] ) ); ?>
							<?php endif; ?>
							<?php if ( ! empty( $event['event_time'] ) ) : ?>
								- <?php echo esc_html( $event['event_time'] ); ?>
							<?php endif; ?>

							<?php if ( 'goal' === $event['event_type'] ) : ?>
								<?php if ( ! empty( $event['scorer'] ) ) : ?>
									- <?php esc_html_e( 'Scorer:', 'mahl-manager' ); ?>
									<a href="<?php echo esc_url( $event['scorer']['permalink'] ); ?>"><?php echo esc_html( $event['scorer']['title'] ); ?></a>
								<?php endif; ?>
								<?php if ( ! empty( $event['assists'] ) ) : ?>
									- <?php esc_html_e( 'Assists:', 'mahl-manager' ); ?>
									<?php
									$assist_links = array();
									foreach ( $event['assists'] as $assist ) {
										$assist_links[] = sprintf(
											'<a href="%1$s">%2$s</a>',
											esc_url( $assist['permalink'] ),
											esc_html( $assist['title'] )
										);
									}
									echo wp_kses_post( implode( ', ', $assist_links ) );
									?>
								<?php endif; ?>
							<?php endif; ?>

							<?php if ( 'penalty' === $event['event_type'] ) : ?>
								<?php if ( ! empty( $event['penalized_player'] ) ) : ?>
									- <?php esc_html_e( 'Player:', 'mahl-manager' ); ?>
									<a href="<?php echo esc_url( $event['penalized_player']['permalink'] ); ?>"><?php echo esc_html( $event['penalized_player']['title'] ); ?></a>
								<?php endif; ?>
								<?php if ( ! empty( $event['penalty_minutes'] ) ) : ?>
									- <?php echo esc_html( sprintf( __( '%d minutes', 'mahl-manager' ), $event['penalty_minutes'] ) ); ?>
								<?php endif; ?>
							<?php endif; ?>

							<?php if ( ! empty( $event['label'] ) ) : ?>
								- <?php echo esc_html( $event['label'] ); ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'No game events have been recorded yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>
	</article>
</main>
<?php
get_footer();
