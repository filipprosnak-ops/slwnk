<?php
/**
 * Phases form view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

$is_edit = !empty($phase->id);
?>

<div class="wrap">
	<h1><?php echo $is_edit ? 'Upraviť fázu' : 'Pridať fázu'; ?></h1>

	<?php if (empty($seasons)) : ?>
		<div class="notice notice-warning">
			<p>Najprv musíš vytvoriť aspoň jednu sezónu.</p>
		</div>
	<?php else : ?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="mahl_save_phase">
			<input type="hidden" name="phase_id" value="<?php echo esc_attr((string) $phase->id); ?>">
			<?php wp_nonce_field('mahl_save_phase'); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="season_id">Sezóna</label>
						</th>
						<td>
							<select name="season_id" id="season_id" required>
								<option value="">-- Vyber sezónu --</option>
								<?php foreach ($seasons as $season) : ?>
									<option value="<?php echo esc_attr((string) $season->id); ?>" <?php selected((int) $phase->season_id, (int) $season->id); ?>>
										<?php echo esc_html((string) $season->name); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

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
								value="<?php echo esc_attr((string) $phase->name); ?>"
								required
							>
							<p class="description">Príklad: Základná časť, TOP 4, BOTTOM 4, Playoff</p>
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
								value="<?php echo esc_attr((string) $phase->slug); ?>"
							>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="type">Typ</label>
						</th>
						<td>
							<select name="type" id="type">
								<option value="regular" <?php selected($phase->type, 'regular'); ?>>Základná časť</option>
								<option value="top" <?php selected($phase->type, 'top'); ?>>TOP</option>
								<option value="bottom" <?php selected($phase->type, 'bottom'); ?>>BOTTOM</option>
								<option value="playoff" <?php selected($phase->type, 'playoff'); ?>>Playoff</option>
								<option value="allstar" <?php selected($phase->type, 'allstar'); ?>>All-Star</option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="sort_order">Poradie</label>
						</th>
						<td>
							<input
								name="sort_order"
								type="number"
								id="sort_order"
								class="small-text"
								value="<?php echo esc_attr((string) $phase->sort_order); ?>"
								min="0"
								step="1"
							>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button($is_edit ? 'Uložiť zmeny' : 'Uložiť fázu'); ?>
		</form>
	<?php endif; ?>
</div>