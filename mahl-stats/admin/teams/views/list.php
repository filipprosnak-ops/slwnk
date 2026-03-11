<?php
/**
 * Teams list view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">Tímy</h1>

	<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-teams&action=add')); ?>" class="page-title-action">
		Pridať tím
	</a>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'created') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Tím bol úspešne vytvorený.</p>
		</div>
	<?php endif; ?>

	<?php if (isset($_GET['message']) && $_GET['message'] === 'updated') : ?>
		<div class="notice notice-success is-dismissible">
			<p>Tím bol úspešne upravený.</p>
		</div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<table class="widefat striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Názov</th>
				<th>Slug</th>
				<th>Mesto</th>
				<th>Rok založenia</th>
				<th>Logo ID</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($teams)) : ?>
				<?php foreach ($teams as $team) : ?>
					<tr>
						<td><?php echo esc_html((string) $team->id); ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-teams&action=edit&team_id=' . (int) $team->id)); ?>">
									<?php echo esc_html((string) $team->name); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-teams&action=edit&team_id=' . (int) $team->id)); ?>">
										Upraviť
									</a>
								</span>
							</div>
						</td>
						<td><?php echo esc_html((string) $team->slug); ?></td>
						<td><?php echo esc_html((string) $team->city); ?></td>
						<td><?php echo esc_html((string) $team->founded_year); ?></td>
						<td><?php echo esc_html((string) $team->logo_id); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="6">Zatiaľ neexistujú žiadne tímy.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>