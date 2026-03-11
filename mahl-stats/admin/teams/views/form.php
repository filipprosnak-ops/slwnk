<?php
/**
 * Teams form view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

$is_edit = !empty($team->id);
?>

<div class="wrap">
	<h1><?php echo $is_edit ? 'Upraviť tím' : 'Pridať tím'; ?></h1>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<input type="hidden" name="action" value="mahl_save_team">
		<input type="hidden" name="team_id" value="<?php echo esc_attr((string) $team->id); ?>">
		<?php wp_nonce_field('mahl_save_team'); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="name">Názov tímu</label>
					</th>
					<td>
						<input
							name="name"
							type="text"
							id="name"
							class="regular-text"
							value="<?php echo esc_attr((string) $team->name); ?>"
							required
						>
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
							value="<?php echo esc_attr((string) $team->slug); ?>"
						>
						<p class="description">Ak necháš prázdne, vygeneruje sa automaticky z názvu.</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="city">Mesto</label>
					</th>
					<td>
						<input
							name="city"
							type="text"
							id="city"
							class="regular-text"
							value="<?php echo esc_attr((string) $team->city); ?>"
						>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="founded_year">Rok založenia</label>
					</th>
					<td>
						<input
							name="founded_year"
							type="number"
							id="founded_year"
							class="small-text"
							value="<?php echo esc_attr((string) $team->founded_year); ?>"
							min="1900"
							max="2100"
							step="1"
						>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="logo_id">Logo ID</label>
					</th>
					<td>
						<input
							name="logo_id"
							type="number"
							id="logo_id"
							class="small-text"
							value="<?php echo esc_attr((string) $team->logo_id); ?>"
							min="0"
							step="1"
						>
						<p class="description">Zatiaľ ručne vlož ID obrázka z WordPress médií.</p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button($is_edit ? 'Uložiť zmeny' : 'Uložiť tím'); ?>
	</form>
</div>