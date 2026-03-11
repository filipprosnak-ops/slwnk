<?php
/**
 * Players form view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

$is_edit = !empty($player->id);
?>

<div class="wrap">
	<h1><?php echo $is_edit ? 'Upraviť hráča' : 'Pridať hráča'; ?></h1>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<input type="hidden" name="action" value="mahl_save_player">
		<input type="hidden" name="player_id" value="<?php echo esc_attr((string) $player->id); ?>">
		<?php wp_nonce_field('mahl_save_player'); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="first_name">Meno</label>
					</th>
					<td>
						<input
							name="first_name"
							type="text"
							id="first_name"
							class="regular-text"
							value="<?php echo esc_attr((string) $player->first_name); ?>"
							required
						>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="last_name">Priezvisko</label>
					</th>
					<td>
						<input
							name="last_name"
							type="text"
							id="last_name"
							class="regular-text"
							value="<?php echo esc_attr((string) $player->last_name); ?>"
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
							value="<?php echo esc_attr((string) $player->slug); ?>"
						>
						<p class="description">Ak necháš prázdne, vygeneruje sa automaticky z mena a priezviska.</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="birth_date">Dátum narodenia</label>
					</th>
					<td>
						<input
							name="birth_date"
							type="date"
							id="birth_date"
							value="<?php echo esc_attr((string) $player->birth_date); ?>"
						>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="default_position">Pozícia</label>
					</th>
					<td>
						<select name="default_position" id="default_position">
							<option value="G" <?php selected($player->default_position, 'G'); ?>>G - Brankár</option>
							<option value="D" <?php selected($player->default_position, 'D'); ?>>D - Obranca</option>
							<option value="F" <?php selected($player->default_position, 'F'); ?>>F - Útočník</option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="shoots">Strana hokejky</label>
					</th>
					<td>
						<select name="shoots" id="shoots">
							<option value="L" <?php selected($player->shoots, 'L'); ?>>L</option>
							<option value="R" <?php selected($player->shoots, 'R'); ?>>R</option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button($is_edit ? 'Uložiť zmeny' : 'Uložiť hráča'); ?>
	</form>
</div>