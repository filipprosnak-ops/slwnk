<?php
/**
 * Game roster view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

$goal_strength_labels = [
	'even'        => 'Rovnovážny stav',
	'powerplay'   => 'Presilovka',
	'shorthanded' => 'Oslabenie',
	'shootout'    => 'Samostatný nájazd',
];

$official_role_labels = [
	'referee'   => 'Rozhodca',
	'linesman'  => 'Čiarový',
	'timekeeper'=> 'Časomiera',
	'other'     => 'Iné',
];

$note_type_labels = [
	'general'    => 'Všeobecná',
	'discipline' => 'Disciplinárna',
	'admin'      => 'Administratívna',
	'stream'     => 'Stream / prenos',
];
?>

<div class="wrap">
	<h1>Zápis zápasu – kompletný zápis</h1>

	<p>
		<strong>Zápas:</strong>
		<?php echo esc_html((string) $home_team->name . ' vs ' . (string) $away_team->name); ?>
	</p>

	<p>
		<strong>Dátum:</strong>
		<?php echo esc_html((string) $game->game_date); ?>
	</p>

	<p>
		<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-games')); ?>" class="button">Späť na zápasy</a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=mahl-games&action=edit&game_id=' . (int) $game->id)); ?>" class="button">Upraviť základ zápasu</a>
	</p>

	<?php
	$messages = [
		'roster_added'     => 'Hráč bol pridaný do súpisky.',
		'roster_deleted'   => 'Hráč bol odstránený zo súpisky.',
		'roster_exists'    => 'Tento hráč už v súpiske je.',
		'goal_added'       => 'Gól bol pridaný.',
		'goal_deleted'     => 'Gól bol odstránený.',
		'penalty_added'    => 'Trest bol pridaný.',
		'penalty_deleted'  => 'Trest bol odstránený.',
		'goalie_added'     => 'Brankár bol pridaný.',
		'goalie_deleted'   => 'Brankár bol odstránený.',
		'official_added'   => 'Rozhodca bol pridaný.',
		'official_deleted' => 'Rozhodca bol odstránený.',
		'note_added'       => 'Poznámka bola pridaná.',
		'note_deleted'     => 'Poznámka bola odstránená.',
	];

	if (isset($_GET['message']) && isset($messages[$_GET['message']])) :
		$is_warning = $_GET['message'] === 'roster_exists';
		?>
		<div class="notice <?php echo $is_warning ? 'notice-warning' : 'notice-success'; ?> is-dismissible">
			<p><?php echo esc_html($messages[$_GET['message']]); ?></p>
		</div>
	<?php endif; ?>

	<hr>

	<h2>Domáci tím: <?php echo esc_html((string) $home_team->name); ?></h2>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:20px;">
		<input type="hidden" name="action" value="mahl_add_game_roster">
		<input type="hidden" name="game_id" value="<?php echo esc_attr((string) $game->id); ?>">
		<input type="hidden" name="team_id" value="<?php echo esc_attr((string) $home_team->id); ?>">
		<?php wp_nonce_field('mahl_add_game_roster'); ?>

		<select name="registration_id" required>
			<option value="">-- Vyber hráča domácich --</option>
			<?php foreach ($home_available as $registration) : ?>
				<option value="<?php echo esc_attr((string) $registration->id); ?>">
					<?php echo esc_html(trim((string) $registration->last_name . ' ' . (string) $registration->first_name . ' #' . (string) $registration->jersey_number . ' (' . (string) $registration->position . ')')); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php submit_button('Pridať do súpisky domácich', 'secondary', 'submit', false); ?>
	</form>

	<table class="widefat striped" style="margin-bottom:30px;">
		<thead>
			<tr>
				<th>Hráč</th>
				<th>Číslo</th>
				<th>Role</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($home_roster)) : ?>
				<?php foreach ($home_roster as $row) : ?>
					<tr>
						<td><?php echo esc_html(trim((string) $row->first_name . ' ' . (string) $row->last_name)); ?></td>
						<td><?php echo esc_html((string) $row->jersey_number); ?></td>
						<td><?php echo esc_html((string) $row->role); ?></td>
						<td>
							<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mahl_delete_game_roster&roster_id=' . (int) $row->id . '&game_id=' . (int) $game->id), 'mahl_delete_roster_' . (int) $row->id)); ?>" onclick="return confirm('Odstrániť hráča zo súpisky?');">Odstrániť</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="4">Súpiska domácich je zatiaľ prázdna.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>

	<h2>Hosťujúci tím: <?php echo esc_html((string) $away_team->name); ?></h2>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:20px;">
		<input type="hidden" name="action" value="mahl_add_game_roster">
		<input type="hidden" name="game_id" value="<?php echo esc_attr((string) $game->id); ?>">
		<input type="hidden" name="team_id" value="<?php echo esc_attr((string) $away_team->id); ?>">
		<?php wp_nonce_field('mahl_add_game_roster'); ?>

		<select name="registration_id" required>
			<option value="">-- Vyber hráča hostí --</option>
			<?php foreach ($away_available as $registration) : ?>
				<option value="<?php echo esc_attr((string) $registration->id); ?>">
					<?php echo esc_html(trim((string) $registration->last_name . ' ' . (string) $registration->first_name . ' #' . (string) $registration->jersey_number . ' (' . (string) $registration->position . ')')); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php submit_button('Pridať do súpisky hostí', 'secondary', 'submit', false); ?>
	</form>

	<table class="widefat striped" style="margin-bottom:30px;">
		<thead>
			<tr>
				<th>Hráč</th>
				<th>Číslo</th>
				<th>Role</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($away_roster)) : ?>
				<?php foreach ($away_roster as $row) : ?>
					<tr>
						<td><?php echo esc_html(trim((string) $row->first_name . ' ' . (string) $row->last_name)); ?></td>
						<td><?php echo esc_html((string) $row->jersey_number); ?></td>
						<td><?php echo esc_html((string) $row->role); ?></td>
						<td>
							<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mahl_delete_game_roster&roster_id=' . (int) $row->id . '&game_id=' . (int) $game->id), 'mahl_delete_roster_' . (int) $row->id)); ?>" onclick="return confirm('Odstrániť hráča zo súpisky?');">Odstrániť</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="4">Súpiska hostí je zatiaľ prázdna.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>

	<hr>

	<h2>Góly</h2>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:20px;">
		<input type="hidden" name="action" value="mahl_add_game_goal">
		<input type="hidden" name="game_id" value="<?php echo esc_attr((string) $game->id); ?>">
		<?php wp_nonce_field('mahl_add_game_goal'); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th><label for="team_id">Tím</label></th>
					<td>
						<select name="team_id" id="team_id" required>
							<option value="">-- Vyber tím --</option>
							<option value="<?php echo esc_attr((string) $home_team->id); ?>"><?php echo esc_html((string) $home_team->name); ?></option>
							<option value="<?php echo esc_attr((string) $away_team->id); ?>"><?php echo esc_html((string) $away_team->name); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="scorer_player_id">Strelec</label></th>
					<td>
						<select name="scorer_player_id" id="scorer_player_id" required>
							<option value="">-- Vyber strelca --</option>
							<optgroup label="<?php echo esc_attr((string) $home_team->name); ?>">
								<?php foreach ($home_roster as $row) : ?>
									<option value="<?php echo esc_attr((string) $row->player_id); ?>">
										<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
							<optgroup label="<?php echo esc_attr((string) $away_team->name); ?>">
								<?php foreach ($away_roster as $row) : ?>
									<option value="<?php echo esc_attr((string) $row->player_id); ?>">
										<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
						</select>
						<p class="description">Vyber aj správny tím. Hráč musí byť v súpiske daného tímu.</p>
					</td>
				</tr>
				<tr>
					<th><label for="assist_1_player_id">Asistencia 1</label></th>
					<td>
						<select name="assist_1_player_id" id="assist_1_player_id">
							<option value="">-- Bez asistencie 1 --</option>
							<optgroup label="<?php echo esc_attr((string) $home_team->name); ?>">
								<?php foreach ($home_roster as $row) : ?>
									<option value="<?php echo esc_attr((string) $row->player_id); ?>">
										<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
							<optgroup label="<?php echo esc_attr((string) $away_team->name); ?>">
								<?php foreach ($away_roster as $row) : ?>
									<option value="<?php echo esc_attr((string) $row->player_id); ?>">
										<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="assist_2_player_id">Asistencia 2</label></th>
					<td>
						<select name="assist_2_player_id" id="assist_2_player_id">
							<option value="">-- Bez asistencie 2 --</option>
							<optgroup label="<?php echo esc_attr((string) $home_team->name); ?>">
								<?php foreach ($home_roster as $row) : ?>
									<option value="<?php echo esc_attr((string) $row->player_id); ?>">
										<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
							<optgroup label="<?php echo esc_attr((string) $away_team->name); ?>">
								<?php foreach ($away_roster as $row) : ?>
									<option value="<?php echo esc_attr((string) $row->player_id); ?>">
										<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="part_number">Časť zápasu</label></th>
					<td><input name="part_number" type="number" id="part_number" class="small-text" value="1" min="1" step="1"></td>
				</tr>
				<tr>
					<th><label for="minute">Minúta</label></th>
					<td><input name="minute" type="number" id="minute" class="small-text" value="0" min="0" step="1"></td>
				</tr>
				<tr>
					<th><label for="second">Sekunda</label></th>
					<td><input name="second" type="number" id="second" class="small-text" value="0" min="0" max="59" step="1"></td>
				</tr>
				<tr>
					<th><label for="strength">Typ gólu</label></th>
					<td>
						<select name="strength" id="strength">
							<option value="even">Rovnovážny stav</option>
							<option value="powerplay">Presilovka</option>
							<option value="shorthanded">Oslabenie</option>
							<option value="shootout">Samostatný nájazd</option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="is_game_winning_goal">Víťazný gól</label></th>
					<td><label><input name="is_game_winning_goal" type="checkbox" id="is_game_winning_goal" value="1"> Áno</label></td>
				</tr>
			</tbody>
		</table>

		<?php submit_button('Pridať gól', 'primary', 'submit', false); ?>
	</form>

	<table class="widefat striped" style="margin-bottom:30px;">
		<thead>
			<tr>
				<th>Tím</th>
				<th>Strelec</th>
				<th>Asistencia 1</th>
				<th>Asistencia 2</th>
				<th>Časť</th>
				<th>Čas</th>
				<th>Typ</th>
				<th>GWG</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($goals)) : ?>
				<?php foreach ($goals as $goal) : ?>
					<tr>
						<td><?php echo esc_html((string) $goal->team_name); ?></td>
						<td><?php echo esc_html(trim((string) $goal->scorer_first_name . ' ' . (string) $goal->scorer_last_name)); ?></td>
						<td><?php echo esc_html(trim((string) $goal->assist1_first_name . ' ' . (string) $goal->assist1_last_name)); ?></td>
						<td><?php echo esc_html(trim((string) $goal->assist2_first_name . ' ' . (string) $goal->assist2_last_name)); ?></td>
						<td><?php echo esc_html((string) $goal->part_number); ?></td>
						<td><?php echo esc_html(sprintf('%02d:%02d', (int) $goal->minute, (int) $goal->second)); ?></td>
						<td><?php echo esc_html($goal_strength_labels[(string) $goal->strength] ?? (string) $goal->strength); ?></td>
						<td><?php echo (int) $goal->is_game_winning_goal === 1 ? 'Áno' : 'Nie'; ?></td>
						<td><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mahl_delete_game_goal&goal_id=' . (int) $goal->id . '&game_id=' . (int) $game->id), 'mahl_delete_goal_' . (int) $goal->id)); ?>" onclick="return confirm('Odstrániť gól?');">Odstrániť</a></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="9">Zatiaľ nie sú zapísané žiadne góly.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>

	<hr>

	<h2>Tresty</h2>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:20px;">
		<input type="hidden" name="action" value="mahl_add_game_penalty">
		<input type="hidden" name="game_id" value="<?php echo esc_attr((string) $game->id); ?>">
		<?php wp_nonce_field('mahl_add_game_penalty'); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th><label for="penalty_team_id">Tím</label></th>
					<td>
						<select name="team_id" id="penalty_team_id" required>
							<option value="">-- Vyber tím --</option>
							<option value="<?php echo esc_attr((string) $home_team->id); ?>"><?php echo esc_html((string) $home_team->name); ?></option>
							<option value="<?php echo esc_attr((string) $away_team->id); ?>"><?php echo esc_html((string) $away_team->name); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="player_id">Hráč</label></th>
					<td>
						<select name="player_id" id="player_id" required>
							<option value="">-- Vyber hráča --</option>
							<optgroup label="<?php echo esc_attr((string) $home_team->name); ?>">
								<?php foreach ($home_roster as $row) : ?>
									<option value="<?php echo esc_attr((string) $row->player_id); ?>">
										<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
							<optgroup label="<?php echo esc_attr((string) $away_team->name); ?>">
								<?php foreach ($away_roster as $row) : ?>
									<option value="<?php echo esc_attr((string) $row->player_id); ?>">
										<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
						</select>
						<p class="description">Vyber aj správny tím. Hráč musí byť v súpiske daného tímu.</p>
					</td>
				</tr>
				<tr>
					<th><label for="penalty_part_number">Časť zápasu</label></th>
					<td><input name="part_number" type="number" id="penalty_part_number" class="small-text" value="1" min="1" step="1"></td>
				</tr>
				<tr>
					<th><label for="penalty_minute">Minúta</label></th>
					<td><input name="minute" type="number" id="penalty_minute" class="small-text" value="0" min="0" step="1"></td>
				</tr>
				<tr>
					<th><label for="penalty_second">Sekunda</label></th>
					<td><input name="second" type="number" id="penalty_second" class="small-text" value="0" min="0" max="59" step="1"></td>
				</tr>
				<tr>
					<th><label for="penalty_minutes">Trestné minúty</label></th>
					<td><input name="penalty_minutes" type="number" id="penalty_minutes" class="small-text" value="2" min="1" step="1"></td>
				</tr>
				<tr>
					<th><label for="reason">Dôvod</label></th>
					<td><input name="reason" type="text" id="reason" class="regular-text" value=""></td>
				</tr>
			</tbody>
		</table>

		<?php submit_button('Pridať trest', 'primary', 'submit', false); ?>
	</form>

	<table class="widefat striped" style="margin-bottom:30px;">
		<thead>
			<tr>
				<th>Tím</th>
				<th>Hráč</th>
				<th>Časť</th>
				<th>Čas</th>
				<th>Minúty</th>
				<th>Dôvod</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($penalties)) : ?>
				<?php foreach ($penalties as $penalty) : ?>
					<tr>
						<td><?php echo esc_html((string) $penalty->team_name); ?></td>
						<td><?php echo esc_html(trim((string) $penalty->first_name . ' ' . (string) $penalty->last_name)); ?></td>
						<td><?php echo esc_html((string) $penalty->part_number); ?></td>
						<td><?php echo esc_html(sprintf('%02d:%02d', (int) $penalty->minute, (int) $penalty->second)); ?></td>
						<td><?php echo esc_html((string) $penalty->penalty_minutes); ?></td>
						<td><?php echo esc_html((string) $penalty->reason); ?></td>
						<td><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mahl_delete_game_penalty&penalty_id=' . (int) $penalty->id . '&game_id=' . (int) $game->id), 'mahl_delete_penalty_' . (int) $penalty->id)); ?>" onclick="return confirm('Odstrániť trest?');">Odstrániť</a></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="7">Zatiaľ nie sú zapísané žiadne tresty.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>

	<hr>

	<h2>Brankári</h2>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:20px;">
		<input type="hidden" name="action" value="mahl_add_game_goalie">
		<input type="hidden" name="game_id" value="<?php echo esc_attr((string) $game->id); ?>">
		<?php wp_nonce_field('mahl_add_game_goalie'); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th><label for="goalie_team_id">Tím</label></th>
					<td>
						<select name="team_id" id="goalie_team_id" required>
							<option value="">-- Vyber tím --</option>
							<option value="<?php echo esc_attr((string) $home_team->id); ?>"><?php echo esc_html((string) $home_team->name); ?></option>
							<option value="<?php echo esc_attr((string) $away_team->id); ?>"><?php echo esc_html((string) $away_team->name); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="goalie_player_id">Brankár</label></th>
					<td>
						<select name="player_id" id="goalie_player_id" required>
							<option value="">-- Vyber brankára --</option>
							<optgroup label="<?php echo esc_attr((string) $home_team->name); ?>">
								<?php foreach ($home_roster as $row) : ?>
									<?php if ((string) $row->role === 'goalie') : ?>
										<option value="<?php echo esc_attr((string) $row->player_id); ?>">
											<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
										</option>
									<?php endif; ?>
								<?php endforeach; ?>
							</optgroup>
							<optgroup label="<?php echo esc_attr((string) $away_team->name); ?>">
								<?php foreach ($away_roster as $row) : ?>
									<?php if ((string) $row->role === 'goalie') : ?>
										<option value="<?php echo esc_attr((string) $row->player_id); ?>">
											<?php echo esc_html(trim((string) $row->last_name . ' ' . (string) $row->first_name . ' #' . (string) $row->jersey_number)); ?>
										</option>
									<?php endif; ?>
								<?php endforeach; ?>
							</optgroup>
						</select>
						<p class="description">Brankár musí byť v súpiske daného tímu a mať rolu goalie.</p>
					</td>
				</tr>
				<tr>
					<th><label for="minutes_played">Odchytané minúty</label></th>
					<td><input name="minutes_played" type="number" id="minutes_played" class="small-text" value="0" min="0" step="1"></td>
				</tr>
				<tr>
					<th><label for="goals_allowed">Inkasované góly</label></th>
					<td><input name="goals_allowed" type="number" id="goals_allowed" class="small-text" value="0" min="0" step="1"></td>
				</tr>
				<tr>
					<th><label for="saves">Zákroky</label></th>
					<td><input name="saves" type="number" id="saves" class="small-text" value="0" min="0" step="1"></td>
				</tr>
				<tr>
					<th><label for="shots_against">Strely na bránu</label></th>
					<td><input name="shots_against" type="number" id="shots_against" class="small-text" value="0" min="0" step="1"></td>
				</tr>
				<tr>
					<th><label for="shutout">Shutout</label></th>
					<td><label><input name="shutout" type="checkbox" id="shutout" value="1"> Áno</label></td>
				</tr>
			</tbody>
		</table>

		<?php submit_button('Pridať brankára', 'primary', 'submit', false); ?>
	</form>

	<table class="widefat striped" style="margin-bottom:30px;">
		<thead>
			<tr>
				<th>Tím</th>
				<th>Brankár</th>
				<th>Minúty</th>
				<th>Inkasované góly</th>
				<th>Zákroky</th>
				<th>Strely</th>
				<th>Shutout</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($goalies)) : ?>
				<?php foreach ($goalies as $goalie) : ?>
					<tr>
						<td><?php echo esc_html((string) $goalie->team_name); ?></td>
						<td><?php echo esc_html(trim((string) $goalie->first_name . ' ' . (string) $goalie->last_name)); ?></td>
						<td><?php echo esc_html((string) $goalie->minutes_played); ?></td>
						<td><?php echo esc_html((string) $goalie->goals_allowed); ?></td>
						<td><?php echo esc_html((string) $goalie->saves); ?></td>
						<td><?php echo esc_html((string) $goalie->shots_against); ?></td>
						<td><?php echo (int) $goalie->shutout === 1 ? 'Áno' : 'Nie'; ?></td>
						<td><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mahl_delete_game_goalie&goalie_id=' . (int) $goalie->id . '&game_id=' . (int) $game->id), 'mahl_delete_goalie_' . (int) $goalie->id)); ?>" onclick="return confirm('Odstrániť brankára?');">Odstrániť</a></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="8">Zatiaľ nie sú zapísaní žiadni brankári.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>

	<hr>

	<h2>Rozhodcovia</h2>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:20px;">
		<input type="hidden" name="action" value="mahl_add_game_official">
		<input type="hidden" name="game_id" value="<?php echo esc_attr((string) $game->id); ?>">
		<?php wp_nonce_field('mahl_add_game_official'); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th><label for="official_name">Meno rozhodcu</label></th>
					<td><input name="official_name" type="text" id="official_name" class="regular-text" value="" required></td>
				</tr>
				<tr>
					<th><label for="official_role">Rola</label></th>
					<td>
						<select name="role" id="official_role">
							<option value="referee">Rozhodca</option>
							<option value="linesman">Čiarový</option>
							<option value="timekeeper">Časomiera</option>
							<option value="other">Iné</option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="official_sort_order">Poradie</label></th>
					<td><input name="sort_order" type="number" id="official_sort_order" class="small-text" value="0" min="0" step="1"></td>
				</tr>
			</tbody>
		</table>

		<?php submit_button('Pridať rozhodcu', 'primary', 'submit', false); ?>
	</form>

	<table class="widefat striped" style="margin-bottom:30px;">
		<thead>
			<tr>
				<th>Meno</th>
				<th>Rola</th>
				<th>Poradie</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($officials)) : ?>
				<?php foreach ($officials as $official) : ?>
					<tr>
						<td><?php echo esc_html((string) $official->official_name); ?></td>
						<td><?php echo esc_html($official_role_labels[(string) $official->role] ?? (string) $official->role); ?></td>
						<td><?php echo esc_html((string) $official->sort_order); ?></td>
						<td><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mahl_delete_game_official&official_id=' . (int) $official->id . '&game_id=' . (int) $game->id), 'mahl_delete_official_' . (int) $official->id)); ?>" onclick="return confirm('Odstrániť rozhodcu?');">Odstrániť</a></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="4">Zatiaľ nie sú zapísaní žiadni rozhodcovia.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>

	<hr>

	<h2>Poznámky</h2>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:20px;">
		<input type="hidden" name="action" value="mahl_add_game_note">
		<input type="hidden" name="game_id" value="<?php echo esc_attr((string) $game->id); ?>">
		<?php wp_nonce_field('mahl_add_game_note'); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th><label for="note_type">Typ poznámky</label></th>
					<td>
						<select name="note_type" id="note_type">
							<option value="general">Všeobecná</option>
							<option value="discipline">Disciplinárna</option>
							<option value="admin">Administratívna</option>
							<option value="stream">Stream / prenos</option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="content">Obsah</label></th>
					<td><textarea name="content" id="content" class="large-text" rows="4" required></textarea></td>
				</tr>
			</tbody>
		</table>

		<?php submit_button('Pridať poznámku', 'primary', 'submit', false); ?>
	</form>

	<table class="widefat striped">
		<thead>
			<tr>
				<th>Typ</th>
				<th>Obsah</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($notes)) : ?>
				<?php foreach ($notes as $note) : ?>
					<tr>
						<td><?php echo esc_html($note_type_labels[(string) $note->note_type] ?? (string) $note->note_type); ?></td>
						<td><?php echo esc_html((string) $note->content); ?></td>
						<td><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mahl_delete_game_note&note_id=' . (int) $note->id . '&game_id=' . (int) $game->id), 'mahl_delete_note_' . (int) $note->id)); ?>" onclick="return confirm('Odstrániť poznámku?');">Odstrániť</a></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="3">Zatiaľ nie sú zapísané žiadne poznámky.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>