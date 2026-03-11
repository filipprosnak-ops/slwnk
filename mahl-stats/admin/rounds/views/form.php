<?php
/**
 * Rounds form view.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

$is_edit = !empty($round->id);
?>

<div class="wrap">
	<h1><?php echo $is_edit ? 'Upraviť kolo' : 'Pridať kolo'; ?></h1>

	<?php if (empty($phases)) : ?>
		<div class="notice notice-warning">
			<p>Najprv musíš vytvoriť aspoň jednu fázu.</p>
		</div>
	<?php else : ?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="mahl_save_round">
			<input type="hidden" name="round_id" value="<?php echo esc_attr((string) $round->id); ?>">
			<?php wp_nonce_field('mahl_save_round'); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="phase_id">Fáza</label>
						</th>
						<td>
							<select name="phase_id" id="phase_id" required>
								<option value="">-- Vyber fázu --</option>
								<?php foreach ($phases as $phase) : ?>
									<option value="<?php echo esc_attr((string) $phase->id); ?>" <?php selected((int) $round->phase_id, (int) $phase->id); ?>>
										<?php echo esc_html((string) $phase->season_name . ' → ' . $phase->name); ?>
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
								value="<?php echo esc_attr((string) $round->name); ?>"
								required
							>
							<p class="description">Príklad: 1. kolo, 2. kolo, Semifinále 1</p>
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
								value="<?php echo esc_attr((string) $round->slug); ?>"
							>
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
								value="<?php echo esc_attr((string) $round->sort_order); ?>"
								min="0"
								step="1"
							>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button($is_edit ? 'Uložiť zmeny' : 'Uložiť kolo'); ?>
		</form>
	<?php endif; ?>
</div>