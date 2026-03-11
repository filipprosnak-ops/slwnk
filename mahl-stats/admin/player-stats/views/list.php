<?php
/**
 * Player stats list view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1>Hráčske štatistiky</h1>

	<form method="get" action="">
		<input type="hidden" name="page" value="mahl-player-stats">

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="season_id">Sezóna</label></th>
					<td>
						<select name="season_id" id="season_id" required>
							<option value="">-- Vyber sezónu --</option>
							<?php foreach ($seasons as $season) : ?>
								<option value="<?php echo esc_attr((string) $season->id); ?>" <?php selected($selected_season_id, (int) $season->id); ?>>
									<?php echo esc_html((string) $season->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="phase_id">Fáza</label></th>
					<td>
						<select name="phase_id" id="phase_id">
							<option value="">-- Všetky fázy --</option>
							<?php foreach ($phases as $phase) : ?>
								<option value="<?php echo esc_attr((string) $phase->id); ?>" <?php selected($selected_phase_id, (int) $phase->id); ?>>
									<?php echo esc_html((string) $phase->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">Ak necháš prázdne, spočítajú sa všetky odohraté zápasy danej sezóny.</p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button('Zobraziť štatistiky'); ?>
	</form>

	<?php if ($selected_season_id > 0) : ?>
		<hr>

		<?php if (!empty($rows)) : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>#</th>
						<th>Hráč</th>
						<th>Tím</th>
						<th>Z</th>
						<th>G</th>
						<th>A</th>
						<th>B</th>
						<th>TM</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td><?php echo esc_html((string) $row['position']); ?></td>
							<td><strong><?php echo esc_html((string) $row['player_name']); ?></strong></td>
							<td><?php echo esc_html((string) $row['team_name']); ?></td>
							<td><?php echo esc_html((string) $row['games']); ?></td>
							<td><?php echo esc_html((string) $row['goals']); ?></td>
							<td><?php echo esc_html((string) $row['assists']); ?></td>
							<td><strong><?php echo esc_html((string) $row['points']); ?></strong></td>
							<td><?php echo esc_html((string) $row['penalty_minutes']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p class="description" style="margin-top:12px;">
				Zoradenie: body, góly, asistencie.
			</p>
		<?php else : ?>
			<div class="notice notice-info">
				<p>Pre zvolený filter zatiaľ neexistujú žiadne hráčske štatistiky.</p>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>