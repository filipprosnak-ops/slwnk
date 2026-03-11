<?php
/**
 * Games list view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">Zápasy</h1>

	<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-games&action=add')); ?>" class="page-title-action">
		Pridať zápas
	</a>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'created') : ?>
		<div class="notice notice-success is-dismissible"><p>Zápas bol úspešne vytvorený.</p></div>
	<?php endif; ?>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'updated') : ?>
		<div class="notice notice-success is-dismissible"><p>Zápas bol úspešne upravený.</p></div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<table class="widefat striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Zápas</th>
				<th>Sezóna</th>
				<th>Fáza</th>
				<th>Kolo</th>
				<th>Dátum</th>
				<th>Skóre</th>
				<th>Stav</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($games)) : ?>
				<?php foreach ($games as $game) : ?>
					<tr>
						<td><?php echo esc_html((string) $game->id); ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-games&action=edit&game_id=' . (int) $game->id)); ?>">
									<?php echo esc_html((string) $game->home_team_name . ' vs ' . (string) $game->away_team_name); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-games&action=edit&game_id=' . (int) $game->id)); ?>">
										Upraviť
									</a>
								</span>
								|
								<span class="view">
									<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . (int) $game->id)); ?>">
										Zápis zápasu
									</a>
								</span>
							</div>
						</td>
						<td><?php echo esc_html((string) $game->season_name); ?></td>
						<td><?php echo esc_html((string) $game->phase_name); ?></td>
						<td><?php echo esc_html((string) $game->round_name); ?></td>
						<td><?php echo esc_html((string) $game->game_date); ?></td>
						<td><?php echo esc_html((string) $game->home_score . ' : ' . (string) $game->away_score); ?></td>
						<td><?php echo esc_html((string) $game->status); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="8">Zatiaľ neexistujú žiadne zápasy.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>