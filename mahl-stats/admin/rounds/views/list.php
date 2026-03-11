<?php
/**
 * Rounds list view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">Kolá</h1>

	<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-rounds&action=add')); ?>" class="page-title-action">
		Pridať kolo
	</a>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'created') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Kolo bolo úspešne vytvorené.</p>
		</div>
	<?php endif; ?>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'updated') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Kolo bolo úspešne upravené.</p>
		</div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<table class="widefat striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Názov</th>
				<th>Sezóna</th>
				<th>Fáza</th>
				<th>Slug</th>
				<th>Poradie</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($rounds)) : ?>
				<?php foreach ($rounds as $round) : ?>
					<tr>
						<td><?php echo esc_html((string) $round->id); ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-rounds&action=edit&round_id=' . (int) $round->id)); ?>">
									<?php echo esc_html((string) $round->name); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-rounds&action=edit&round_id=' . (int) $round->id)); ?>">
										Upraviť
									</a>
								</span>
							</div>
						</td>
						<td><?php echo esc_html((string) $round->season_name); ?></td>
						<td><?php echo esc_html((string) $round->phase_name); ?></td>
						<td><?php echo esc_html((string) $round->slug); ?></td>
						<td><?php echo esc_html((string) $round->sort_order); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="6">Zatiaľ neexistujú žiadne kolá.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>