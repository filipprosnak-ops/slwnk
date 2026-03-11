<?php
/**
 * Seasons list view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">Sezóny</h1>

	<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-seasons&action=add')); ?>" class="page-title-action">
		Pridať sezónu
	</a>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'created') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Sezóna bola úspešne vytvorená.</p>
		</div>
	<?php endif; ?>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'updated') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Sezóna bola úspešne upravená.</p>
		</div>
	<?php endif; ?>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'archived') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Sezóna bola archivovaná.</p>
		</div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<table class="widefat striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Názov</th>
				<th>Slug</th>
				<th>Začiatok</th>
				<th>Koniec</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($seasons)) : ?>
				<?php foreach ($seasons as $season) : ?>
					<tr>
						<td><?php echo esc_html((string) $season->id); ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-seasons&action=edit&season_id=' . (int) $season->id)); ?>">
									<?php echo esc_html((string) $season->name); ?>
								</a>
							</strong>

							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-seasons&action=edit&season_id=' . (int) $season->id)); ?>">
										Upraviť
									</a>
								</span>

								<?php if ((string) $season->status !== 'archived') : ?>
									|
									<span class="trash">
										<a
											href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mahl_archive_season&season_id=' . (int) $season->id), 'mahl_archive_season_' . (int) $season->id)); ?>"
											onclick="return confirm('Naozaj chceš archivovať túto sezónu?');"
										>
											Archivovať
										</a>
									</span>
								<?php endif; ?>
							</div>
						</td>
						<td><?php echo esc_html((string) $season->slug); ?></td>
						<td><?php echo esc_html((string) $season->start_date); ?></td>
						<td><?php echo esc_html((string) $season->end_date); ?></td>
						<td><?php echo esc_html((string) $season->status); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="6">Zatiaľ neexistujú žiadne sezóny.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>