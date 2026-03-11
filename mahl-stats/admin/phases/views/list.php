<?php
/**
 * Phases list view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">Fázy</h1>

	<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-phases&action=add')); ?>" class="page-title-action">
		Pridať fázu
	</a>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'created') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Fáza bola úspešne vytvorená.</p>
		</div>
	<?php endif; ?>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'updated') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Fáza bola úspešne upravená.</p>
		</div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<table class="widefat striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Názov</th>
				<th>Sezóna</th>
				<th>Slug</th>
				<th>Typ</th>
				<th>Poradie</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($phases)) : ?>
				<?php foreach ($phases as $phase) : ?>
					<tr>
						<td><?php echo esc_html((string) $phase->id); ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-phases&action=edit&phase_id=' . (int) $phase->id)); ?>">
									<?php echo esc_html((string) $phase->name); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-phases&action=edit&phase_id=' . (int) $phase->id)); ?>">
										Upraviť
									</a>
								</span>
							</div>
						</td>
						<td><?php echo esc_html((string) $phase->season_name); ?></td>
						<td><?php echo esc_html((string) $phase->slug); ?></td>
						<td><?php echo esc_html((string) $phase->type); ?></td>
						<td><?php echo esc_html((string) $phase->sort_order); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="6">Zatiaľ neexistujú žiadne fázy.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>