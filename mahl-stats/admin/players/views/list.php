<?php
/**
 * Players list view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">Hráči</h1>

	<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-players&action=add')); ?>" class="page-title-action">
		Pridať hráča
	</a>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'created') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Hráč bol úspešne vytvorený.</p>
		</div>
	<?php endif; ?>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'updated') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Hráč bol úspešne upravený.</p>
		</div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<table class="widefat striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Meno a priezvisko</th>
				<th>Slug</th>
				<th>Dátum narodenia</th>
				<th>Pozícia</th>
				<th>Strana hokejky</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($players)) : ?>
				<?php foreach ($players as $player) : ?>
					<tr>
						<td><?php echo esc_html((string) $player->id); ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-players&action=edit&player_id=' . (int) $player->id)); ?>">
									<?php echo esc_html(trim((string) $player->first_name . ' ' . (string) $player->last_name)); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-players&action=edit&player_id=' . (int) $player->id)); ?>">
										Upraviť
									</a>
								</span>
							</div>
						</td>
						<td><?php echo esc_html((string) $player->slug); ?></td>
						<td><?php echo esc_html((string) $player->birth_date); ?></td>
						<td><?php echo esc_html((string) $player->default_position); ?></td>
						<td><?php echo esc_html((string) $player->shoots); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="6">Zatiaľ neexistujú žiadni hráči.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>