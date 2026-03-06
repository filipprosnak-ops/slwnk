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
	<article <?php post_class( 'mahl-layout mahl-layout-game' ); ?>>
		<header class="entry-header mahl-page-header mahl-game-header">
			<div class="mahl-page-header__main">
				<p class="mahl-page-header__eyebrow"><?php esc_html_e( 'Game', 'mahl-manager' ); ?></p>
				<h1 class="entry-title mahl-page-title"><?php echo esc_html( $data['title'] ); ?></h1>
			</div>
			<?php if ( ! empty( $data['status_label'] ) ) : ?>
				<div class="mahl-page-header__context">
					<span class="mahl-status-pill mahl-status-pill--<?php echo esc_attr( $data['status'] ); ?>"><?php echo esc_html( $data['status_label'] ); ?></span>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( ! empty( $data['excerpt'] ) ) : ?>
			<div class="entry-summary mahl-page-summary"><?php echo wp_kses_post( wpautop( $data['excerpt'] ) ); ?></div>
		<?php endif; ?>

		<section class="mahl-section mahl-game-scoreboard" aria-labelledby="mahl-game-scoreboard-title">
			<header class="mahl-section__header">
				<h2 id="mahl-game-scoreboard-title" class="mahl-section__title"><?php esc_html_e( 'Scoreboard', 'mahl-manager' ); ?></h2>
			</header>
			<div class="mahl-scoreboard">
				<div class="mahl-scoreboard__team mahl-scoreboard__team--home">
					<span class="mahl-scoreboard__team-label"><?php esc_html_e( 'Home', 'mahl-manager' ); ?></span>
					<div class="mahl-scoreboard__team-name">
						<?php if ( ! empty( $data['home_team'] ) ) : ?>
							<a href="<?php echo esc_url( $data['home_team']['permalink'] ); ?>"><?php echo esc_html( $data['home_team']['title'] ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'Not assigned', 'mahl-manager' ); ?>
						<?php endif; ?>
					</div>
				</div>
				<div class="mahl-scoreboard__score">
					<span class="mahl-scoreboard__score-main"><?php echo esc_html( $data['score']['display_state'] ); ?></span>
				</div>
				<div class="mahl-scoreboard__team mahl-scoreboard__team--away">
					<span class="mahl-scoreboard__team-label"><?php esc_html_e( 'Away', 'mahl-manager' ); ?></span>
					<div class="mahl-scoreboard__team-name">
						<?php if ( ! empty( $data['away_team'] ) ) : ?>
							<a href="<?php echo esc_url( $data['away_team']['permalink'] ); ?>"><?php echo esc_html( $data['away_team']['title'] ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'Not assigned', 'mahl-manager' ); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>

		<section class="mahl-section mahl-game-summary" aria-labelledby="mahl-game-summary-title">
			<header class="mahl-section__header">
				<h2 id="mahl-game-summary-title" class="mahl-section__title"><?php esc_html_e( 'Game Details', 'mahl-manager' ); ?></h2>
			</header>
			<dl class="mahl-definition-list mahl-definition-list--game">
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
					<dd><?php echo esc_html( $data['group_label'] ); ?></dd>
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

				<dt><?php esc_html_e( 'Final Score', 'mahl-manager' ); ?></dt>
				<dd>
					<?php if ( ! empty( $data['score']['text'] ) ) : ?>
						<?php echo esc_html( $data['score']['text'] ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Not available', 'mahl-manager' ); ?>
					<?php endif; ?>
				</dd>
			</dl>
		</section>

		<?php if ( ! empty( $data['period_scores'] ) ) : ?>
			<section class="mahl-section mahl-game-period-scores" aria-labelledby="mahl-period-scores-title">
				<header class="mahl-section__header">
					<h2 id="mahl-period-scores-title" class="mahl-section__title"><?php esc_html_e( 'Period Scores', 'mahl-manager' ); ?></h2>
				</header>
				<div class="mahl-table-wrap">
					<table class="mahl-table mahl-table--period-scores">
						<thead>
							<tr>
								<th class="mahl-table__cell mahl-table__cell--label"><?php esc_html_e( 'Period', 'mahl-manager' ); ?></th>
								<th class="mahl-table__cell mahl-table__cell--team"><?php echo esc_html( ! empty( $data['home_team'] ) ? $data['home_team']['title'] : __( 'Home', 'mahl-manager' ) ); ?></th>
								<th class="mahl-table__cell mahl-table__cell--team"><?php echo esc_html( ! empty( $data['away_team'] ) ? $data['away_team']['title'] : __( 'Away', 'mahl-manager' ) ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $data['period_scores'] as $score_row ) : ?>
								<tr>
									<td class="mahl-table__cell mahl-table__cell--label"><?php echo esc_html( $score_row['label'] ); ?></td>
									<td class="mahl-table__cell"><?php echo esc_html( null !== $score_row['home_score'] ? $score_row['home_score'] : '-' ); ?></td>
									<td class="mahl-table__cell"><?php echo esc_html( null !== $score_row['away_score'] ? $score_row['away_score'] : '-' ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $data['content'] ) ) : ?>
			<div class="entry-content mahl-page-content"><?php echo wp_kses_post( $data['content'] ); ?></div>
		<?php endif; ?>

		<section class="mahl-section mahl-game-events" aria-labelledby="mahl-game-events-title">
			<header class="mahl-section__header">
				<h2 id="mahl-game-events-title" class="mahl-section__title"><?php esc_html_e( 'Game Events', 'mahl-manager' ); ?></h2>
			</header>
			<?php if ( ! empty( $data['events'] ) ) : ?>
				<ol class="mahl-event-list">
					<?php foreach ( $data['events'] as $event ) : ?>
						<li class="mahl-event-list__item">
							<article class="mahl-event-card">
								<header class="mahl-event-card__header">
									<strong class="mahl-event-card__type"><?php echo esc_html( $event['event_type_label'] ); ?></strong>
									<div class="mahl-event-card__meta">
										<?php if ( ! empty( $event['team'] ) ) : ?>
											<span class="mahl-event-card__team"><a href="<?php echo esc_url( $event['team']['permalink'] ); ?>"><?php echo esc_html( $event['team']['title'] ); ?></a></span>
										<?php endif; ?>
										<?php if ( ! empty( $event['period_number'] ) ) : ?>
											<span class="mahl-event-card__period"><?php echo esc_html( sprintf( __( 'Period %d', 'mahl-manager' ), $event['period_number'] ) ); ?></span>
										<?php endif; ?>
										<?php if ( ! empty( $event['event_time'] ) ) : ?>
											<span class="mahl-event-card__time"><?php echo esc_html( $event['event_time'] ); ?></span>
										<?php endif; ?>
									</div>
								</header>
								<div class="mahl-event-card__body">

							<?php if ( 'goal' === $event['event_type'] ) : ?>
								<?php if ( ! empty( $event['scorer'] ) ) : ?>
									<p class="mahl-event-card__detail"><?php esc_html_e( 'Scorer:', 'mahl-manager' ); ?>
									<a href="<?php echo esc_url( $event['scorer']['permalink'] ); ?>"><?php echo esc_html( $event['scorer']['title'] ); ?></a>
									</p>
								<?php endif; ?>
								<?php if ( ! empty( $event['assists'] ) ) : ?>
									<p class="mahl-event-card__detail"><?php esc_html_e( 'Assists:', 'mahl-manager' ); ?>
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
									</p>
								<?php endif; ?>
							<?php endif; ?>

							<?php if ( 'penalty' === $event['event_type'] ) : ?>
								<?php if ( ! empty( $event['penalized_player'] ) ) : ?>
									<p class="mahl-event-card__detail"><?php esc_html_e( 'Player:', 'mahl-manager' ); ?>
									<a href="<?php echo esc_url( $event['penalized_player']['permalink'] ); ?>"><?php echo esc_html( $event['penalized_player']['title'] ); ?></a>
									</p>
								<?php endif; ?>
								<?php if ( ! empty( $event['penalty_minutes'] ) ) : ?>
									<p class="mahl-event-card__detail"><?php echo esc_html( sprintf( __( '%d minutes', 'mahl-manager' ), $event['penalty_minutes'] ) ); ?></p>
								<?php endif; ?>
							<?php endif; ?>

							<?php if ( ! empty( $event['label'] ) ) : ?>
								<p class="mahl-event-card__detail"><?php echo esc_html( $event['label'] ); ?></p>
							<?php endif; ?>
								</div>
							</article>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p><?php esc_html_e( 'No game events have been recorded yet.', 'mahl-manager' ); ?></p>
			<?php endif; ?>
		</section>
	</article>
</main>
<?php
get_footer();
