<?php
/**
 * Games form view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

$is_edit = !empty($game->id);
$teams_locked = $is_edit;
?>

<div class="wrap">
	<h1><?php echo $is_edit ? 'Upraviť zápas' : 'Pridať zápas'; ?></h1>

	<?php if (empty($seasons) || empty($phases) || empty($teams)) : ?>
		<div class="notice notice-warning">
			<p>Najprv musíš mať vytvorené sezóny, fázy a tímy.</p>
		</div>
	<?php else : ?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="mahl_save_game">
			<input type="hidden" name="game_id" value="<?php echo esc_attr((string) $game->id); ?>">
			<?php wp_nonce_field('mahl_save_game'); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="season_id">Sezóna</label></th>
						<td>
							<select name="season_id" id="season_id" required>
								<option value="">-- Vyber sezónu --</option>
								<?php foreach ($seasons as $season) : ?>
									<option value="<?php echo esc_attr((string) $season->id); ?>" <?php selected((int) $game->season_id, (int) $season->id); ?>>
										<?php echo esc_html((string) $season->name); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="phase_id">Fáza</label></th>
						<td>
							<select name="phase_id" id="phase_id" required>
								<option value="">-- Vyber fázu --</option>
								<?php foreach ($phases as $phase) : ?>
									<option value="<?php echo esc_attr((string) $phase->id); ?>" <?php selected((int) $game->phase_id, (int) $phase->id); ?>>
										<?php echo esc_html((string) $phase->name); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="round_id">Kolo</label></th>
						<td>
							<select name="round_id" id="round_id">
								<option value="">-- Bez kola --</option>
								<?php foreach ($rounds as $round) : ?>
									<option value="<?php echo esc_attr((string) $round->id); ?>" <?php selected((int) $game->round_id, (int) $round->id); ?>>
										<?php echo esc_html((string) $round->name); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="home_team_id">Domáci tím</label></th>
						<td>
							<select name="home_team_id" id="home_team_id" required <?php disabled($teams_locked, true); ?>>
								<option value="">-- Vyber domáci tím --</option>
								<?php foreach ($teams as $team) : ?>
									<option value="<?php echo esc_attr((string) $team->id); ?>" <?php selected((int) $game->home_team_id, (int) $team->id); ?>>
										<?php echo esc_html((string) $team->name); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<?php if ($teams_locked) : ?>
								<input type="hidden" name="home_team_id" value="<?php echo esc_attr((string) $game->home_team_id); ?>">
								<p class="description">Po uložení zápasu už nie je možné meniť domáci tím.</p>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="away_team_id">Hosťujúci tím</label></th>
						<td>
							<select name="away_team_id" id="away_team_id" required <?php disabled($teams_locked, true); ?>>
								<option value="">-- Vyber hosťujúci tím --</option>
								<?php foreach ($teams as $team) : ?>
									<option value="<?php echo esc_attr((string) $team->id); ?>" <?php selected((int) $game->away_team_id, (int) $team->id); ?>>
										<?php echo esc_html((string) $team->name); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<?php if ($teams_locked) : ?>
								<input type="hidden" name="away_team_id" value="<?php echo esc_attr((string) $game->away_team_id); ?>">
								<p class="description">Po uložení zápasu už nie je možné meniť hosťujúci tím.</p>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="game_date">Dátum a čas</label></th>
						<td>
							<input
								name="game_date"
								type="datetime-local"
								id="game_date"
								value="<?php echo esc_attr((string) (!empty($game->game_date) ? str_replace(' ', 'T', substr((string) $game->game_date, 0, 16)) : '')); ?>"
								required
							>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="venue">Štadión / miesto</label></th>
						<td>
							<input
								name="venue"
								type="text"
								id="venue"
								class="regular-text"
								value="<?php echo esc_attr((string) $game->venue); ?>"
							>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="status">Stav zápasu</label></th>
						<td>
							<select name="status" id="status">
								<option value="scheduled" <?php selected($game->status, 'scheduled'); ?>>Naplánovaný</option>
								<option value="finished" <?php selected($game->status, 'finished'); ?>>Odohratý</option>
								<option value="cancelled" <?php selected($game->status, 'cancelled'); ?>>Zrušený</option>
								<option value="forfeit" <?php selected($game->status, 'forfeit'); ?>>Kontumácia</option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="home_score">Skóre domáci</label></th>
						<td>
							<input
								name="home_score"
								type="number"
								id="home_score"
								class="small-text"
								value="<?php echo esc_attr((string) $game->home_score); ?>"
								min="0"
								step="1"
							>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="away_score">Skóre hostia</label></th>
						<td>
							<input
								name="away_score"
								type="number"
								id="away_score"
								class="small-text"
								value="<?php echo esc_attr((string) $game->away_score); ?>"
								min="0"
								step="1"
							>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="is_forfeit">Kontumácia</label></th>
						<td>
							<label>
								<input
									name="is_forfeit"
									type="checkbox"
									id="is_forfeit"
									value="1"
									<?php checked((int) $game->is_forfeit, 1); ?>
								>
								Áno
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="slug">Slug</label></th>
						<td>
							<input
								name="slug"
								type="text"
								id="slug"
								class="regular-text"
								value="<?php echo esc_attr((string) $game->slug); ?>"
							>
							<p class="description">Ak necháš prázdne, vygeneruje sa automaticky.</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="notes">Poznámka</label></th>
						<td>
							<textarea
								name="notes"
								id="notes"
								class="large-text"
								rows="4"
							><?php echo esc_textarea((string) $game->notes); ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button($is_edit ? 'Uložiť zmeny' : 'Uložiť zápas'); ?>
		</form>
	<?php endif; ?>
</div>