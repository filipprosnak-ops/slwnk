<?php
/**
 * Registrations list view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">Registrácie</h1>

	<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-registrations&action=add')); ?>" class="page-title-action">
		Pridať registráciu
	</a>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'created') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Registrácia bola úspešne vytvorená.</p>
		</div>
	<?php endif; ?>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'updated') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Registrácia bola úspešne upravená.</p>
		</div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<table class="widefat striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Hráč</th>
				<th>Tím</th>
				<th>Sezóna</th>
				<th>Číslo</th>
				<th>Pozícia</th>
				<th>Aktívny</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($registrations)) : ?>
				<?php foreach ($registrations as $registration) : ?>
					<tr>
						<td><?php echo esc_html((string) $registration->id); ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-registrations&action=edit&registration_id=' . (int) $registration->id)); ?>">
									<?php echo esc_html(trim((string) $registration->first_name . ' ' . (string) $registration->last_name)); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-registrations&action=edit&registration_id=' . (int) $registration->id)); ?>">
										Upraviť
									</a>
								</span>
							</div>
						</td>
						<td><?php echo esc_html((string) $registration->team_name); ?></td>
						<td><?php echo esc_html((string) $registration->season_name); ?></td>
						<td><?php echo esc_html((string) $registration->jersey_number); ?></td>
						<td><?php echo esc_html((string) $registration->position); ?></td>
						<td><?php echo (int) $registration->active === 1 ? 'Áno' : 'Nie'; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="7">Zatiaľ neexistujú žiadne registrácie.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>