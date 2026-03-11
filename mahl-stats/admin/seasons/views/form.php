<?php
/**
 * Seasons form view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

$is_edit = !empty($season->id);
?>

<div class="wrap">
	<h1><?php echo $is_edit ? 'Upraviť sezónu' : 'Pridať sezónu'; ?></h1>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<input type="hidden" name="action" value="mahl_save_season">
		<input type="hidden" name="season_id" value="<?php echo esc_attr((string) $season->id); ?>">
		<?php wp_nonce_field('mahl_save_season'); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="name">Názov</label>
					</th>
					<td>
						<input
							name="name"
							type="text"
							id="name"
							class="regular-text"
							value="<?php echo esc_attr((string) $season->name); ?>"
							required
						>
						<p class="description">Príklad: MAHL 2026/2027</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="slug">Slug</label>
					</th>
					<td>
						<input
							name="slug"
							type="text"
							id="slug"
							class="regular-text"
							value="<?php echo esc_attr((string) $season->slug); ?>"
						>
						<p class="description">Ak necháš prázdne, vygeneruje sa automaticky z názvu.</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="start_date">Začiatok sezóny</label>
					</th>
					<td>
						<input
							name="start_date"
							type="date"
							id="start_date"
							value="<?php echo esc_attr((string) $season->start_date); ?>"
						>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="end_date">Koniec sezóny</label>
					</th>
					<td>
						<input
							name="end_date"
							type="date"
							id="end_date"
							value="<?php echo esc_attr((string) $season->end_date); ?>"
						>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="status">Status</label>
					</th>
					<td>
						<select name="status" id="status">
							<option value="draft" <?php selected($season->status, 'draft'); ?>>Koncept</option>
							<option value="active" <?php selected($season->status, 'active'); ?>>Aktívna</option>
							<option value="archived" <?php selected($season->status, 'archived'); ?>>Archivovaná</option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button($is_edit ? 'Uložiť zmeny' : 'Uložiť sezónu'); ?>
	</form>
</div>