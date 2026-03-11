<?php
/**
 * Phases admin controller.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Phases_Admin
{
	/**
	 * Render phases admin page.
	 *
	 * @return void
	 */
	public function render(): void
	{
		$action = isset($_GET['action']) ? sanitize_key((string) $_GET['action']) : 'list';

		switch ($action) {
			case 'add':
				$this->render_form();
				break;

			case 'edit':
				$this->render_form($this->get_current_phase());
				break;

			default:
				$this->render_list();
				break;
		}
	}

	/**
	 * Render phases list.
	 *
	 * @return void
	 */
	private function render_list(): void
	{
		global $wpdb;

		$phases_table  = MAHL_Stats_DB::table('phases');
		$seasons_table = MAHL_Stats_DB::table('seasons');

		$phases = $wpdb->get_results(
			"SELECT p.*, s.name AS season_name
			 FROM {$phases_table} p
			 LEFT JOIN {$seasons_table} s ON s.id = p.season_id
			 ORDER BY p.id DESC"
		);

		include MAHL_STATS_PLUGIN_DIR . 'admin/phases/views/list.php';
	}

	/**
	 * Render add/edit form.
	 *
	 * @param object|null $phase Phase object.
	 * @return void
	 */
	private function render_form(?object $phase = null): void
	{
		global $wpdb;

		$seasons_table = MAHL_Stats_DB::table('seasons');
		$seasons = $wpdb->get_results("SELECT * FROM {$seasons_table} ORDER BY id DESC");

		if ($phase === null) {
			$phase = (object) [
				'id'         => 0,
				'season_id'  => 0,
				'name'       => '',
				'slug'       => '',
				'type'       => 'regular',
				'sort_order' => 0,
			];
		}

		include MAHL_STATS_PLUGIN_DIR . 'admin/phases/views/form.php';
	}

	/**
	 * Handle phase save.
	 *
	 * @return void
	 */
	public function handle_save(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_save_phase');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('phases');

		$phase_id    = isset($_POST['phase_id']) ? absint($_POST['phase_id']) : 0;
		$season_id   = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
		$name        = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
		$slug        = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';
		$type        = isset($_POST['type']) ? sanitize_key((string) $_POST['type']) : 'regular';
		$sort_order  = isset($_POST['sort_order']) ? absint($_POST['sort_order']) : 0;

		if ($season_id <= 0) {
			wp_die('Musíš vybrať sezónu.');
		}

		if ($name === '') {
			wp_die('Názov fázy je povinný.');
		}

		if ($slug === '') {
			$slug = sanitize_title($name);
		}

		$allowed_types = ['regular', 'top', 'bottom', 'playoff', 'allstar'];

		if (!in_array($type, $allowed_types, true)) {
			$type = 'regular';
		}

		$existing_slug = $this->get_phase_by_slug_and_season($slug, $season_id);

		if ($existing_slug && (int) $existing_slug->id !== $phase_id) {
			wp_die('Slug fázy už v tejto sezóne existuje.');
		}

		$data = [
			'season_id'  => $season_id,
			'name'       => $name,
			'slug'       => $slug,
			'type'       => $type,
			'sort_order' => $sort_order,
		];

		$formats = [
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
		];

		if ($phase_id > 0) {
			$result = $wpdb->update(
				$table,
				$data,
				['id' => $phase_id],
				$formats,
				['%d']
			);

			if ($result === false) {
				wp_die('Fázu sa nepodarilo upraviť.');
			}

			wp_safe_redirect(admin_url('admin.php?page=mahl-phases&message=updated'));
			exit;
		}

		$result = $wpdb->insert($table, $data, $formats);

		if ($result === false) {
			wp_die('Fázu sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-phases&message=created'));
		exit;
	}

	/**
	 * Get current phase.
	 *
	 * @return object|null
	 */
	private function get_current_phase(): ?object
	{
		$phase_id = isset($_GET['phase_id']) ? absint($_GET['phase_id']) : 0;

		if ($phase_id <= 0) {
			wp_die('Chýba ID fázy.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('phases');

		$phase = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $phase_id)
		);

		if (!$phase) {
			wp_die('Fáza neexistuje.');
		}

		return $phase;
	}

	/**
	 * Get phase by slug and season.
	 *
	 * @param string $slug Slug.
	 * @param int    $season_id Season ID.
	 * @return object|null
	 */
	private function get_phase_by_slug_and_season(string $slug, int $season_id): ?object
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('phases');

		$phase = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE slug = %s AND season_id = %d LIMIT 1",
				$slug,
				$season_id
			)
		);

		return $phase ?: null;
	}
}