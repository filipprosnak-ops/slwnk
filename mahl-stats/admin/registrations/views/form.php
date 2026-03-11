<?php
/**
 * Registrations form view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

$is_edit = !empty($registration->id);
?>

<div class="wrap">
	<h1><?php echo $is_edit ? 'Upraviť registráciu' : 'Pridať registráciu'; ?></h1>

	<?php if (empty($players) || empty($teams) || empty($seasons)) : ?>
		<div class="notice notice-warning">
			<p>Najprv musíš mať vytvorených hráčov, tímy aj sezóny.</p>
		</div>
	<?php else : ?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="mahl_save_registration">
			<input type="hidden" name="registration_id" value="<?php echo esc_attr((string) $registration->id); ?>">
			<?php wp_nonce_field('mahl_save_registration'); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="season_id">Sezóna</label></th>
						<td>
							<select name="season_id" id="season_id" required>
								<option value="">-- Vyber sezónu --</option>
								<?php foreach ($seasons as $season) : ?>
									<option value="<?php echo esc_attr((string) $season->id); ?>" <?php selected((int) $registration->season_id, (int) $season->id); ?>>
										<?php echo esc_html((string) $season->name); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="team_id">Tím</label></th>
						<td>
							<select name="team_id" id="team_id" required>
								<option value="">-- Vyber tím --</option>
								<?php foreach ($teams as $team) : ?>
									<option value="<?php echo esc_attr((string) $team->id); ?>" <?php selected((int) $registration->team_id, (int) $team->id); ?>>
										<?php echo esc_html((string) $team->name); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="player_id">Hráč</label></th>
						<td>
							<select name="player_id" id="player_id" required>
								<option value="">-- Vyber hráča --</option>
								<?php foreach ($players as $player) : ?>
									<option value="<?php echo esc_attr((string) $player->id); ?>" <?php selected((int) $registration->player_id, (int) $player->id); ?>>
										<?php echo esc_html(trim((string) $player->last_name . ' ' . (string) $player->first_name)); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="jersey_number">Číslo dresu</label></th>
						<td>
							<input
								name="jersey_number"
								type="text"
								id="jersey_number"
								class="regular-text"
								value="<?php echo esc_attr((string) $registration->jersey_number); ?>"
								required
							>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="position">Pozícia</label></th>
						<td>
							<select name="position" id="position">
								<option value="G" <?php selected($registration->position, 'G'); ?>>G - Brankár</option>
								<option value="D" <?php selected($registration->position, 'D'); ?>>D - Obranca</option>
								<option value="F" <?php selected($registration->position, 'F'); ?>>F - Útočník</option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="active">Aktívny</label></th>
						<td>
							<label>
								<input
									name="active"
									type="checkbox"
									id="active"
									value="1"
									<?php checked((int) $registration->active, 1); ?>
								>
								Áno
							</label>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button($is_edit ? 'Uložiť zmeny' : 'Uložiť registráciu'); ?>
		</form>
	<?php endif; ?>
</div>