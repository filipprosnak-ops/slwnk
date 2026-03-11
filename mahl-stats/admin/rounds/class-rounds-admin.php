<?php
/**
 * Rounds admin controller.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Rounds_Admin
{
	/**
	 * Render rounds admin page.
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
				$this->render_form($this->get_current_round());
				break;

			default:
				$this->render_list();
				break;
		}
	}

	/**
	 * Render rounds list.
	 *
	 * @return void
	 */
	private function render_list(): void
	{
		global $wpdb;

		$rounds_table = MAHL_Stats_DB::table('rounds');
		$phases_table = MAHL_Stats_DB::table('phases');
		$seasons_table = MAHL_Stats_DB::table('seasons');

		$rounds = $wpdb->get_results(
			"SELECT r.*, p.name AS phase_name, s.name AS season_name
			 FROM {$rounds_table} r
			 LEFT JOIN {$phases_table} p ON p.id = r.phase_id
			 LEFT JOIN {$seasons_table} s ON s.id = p.season_id
			 ORDER BY r.id DESC"
		);

		include MAHL_STATS_PLUGIN_DIR . 'admin/rounds/views/list.php';
	}

	/**
	 * Render add/edit form.
	 *
	 * @param object|null $round Round object.
	 * @return void
	 */
	private function render_form(?object $round = null): void
	{
		global $wpdb;

		$phases_table  = MAHL_Stats_DB::table('phases');
		$seasons_table = MAHL_Stats_DB::table('seasons');

		$phases = $wpdb->get_results(
			"SELECT p.*, s.name AS season_name
			 FROM {$phases_table} p
			 LEFT JOIN {$seasons_table} s ON s.id = p.season_id
			 ORDER BY s.id DESC, p.sort_order ASC, p.id ASC"
		);

		if ($round === null) {
			$round = (object) [
				'id'         => 0,
				'phase_id'   => 0,
				'name'       => '',
				'slug'       => '',
				'sort_order' => 0,
			];
		}

		include MAHL_STATS_PLUGIN_DIR . 'admin/rounds/views/form.php';
	}

	/**
	 * Handle round save.
	 *
	 * @return void
	 */
	public function handle_save(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_save_round');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$table      = MAHL_Stats_DB::table('rounds');
		$round_id   = isset($_POST['round_id']) ? absint($_POST['round_id']) : 0;
		$phase_id   = isset($_POST['phase_id']) ? absint($_POST['phase_id']) : 0;
		$name       = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
		$slug       = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';
		$sort_order = isset($_POST['sort_order']) ? absint($_POST['sort_order']) : 0;

		if ($phase_id <= 0) {
			wp_die('Musíš vybrať fázu.');
		}

		if ($name === '') {
			wp_die('Názov kola je povinný.');
		}

		if ($slug === '') {
			$slug = sanitize_title($name);
		}

		$existing_slug = $this->get_round_by_slug_and_phase($slug, $phase_id);

		if ($existing_slug && (int) $existing_slug->id !== $round_id) {
			wp_die('Slug kola už v tejto fáze existuje.');
		}

		$data = [
			'phase_id'   => $phase_id,
			'name'       => $name,
			'slug'       => $slug,
			'sort_order' => $sort_order,
		];

		$formats = [
			'%d',
			'%s',
			'%s',
			'%d',
		];

		if ($round_id > 0) {
			$result = $wpdb->update(
				$table,
				$data,
				['id' => $round_id],
				$formats,
				['%d']
			);

			if ($result === false) {
				wp_die('Kolo sa nepodarilo upraviť.');
			}

			wp_safe_redirect(admin_url('admin.php?page=mahl-rounds&message=updated'));
			exit;
		}

		$result = $wpdb->insert($table, $data, $formats);

		if ($result === false) {
			wp_die('Kolo sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-rounds&message=created'));
		exit;
	}

	/**
	 * Get current round.
	 *
	 * @return object|null
	 */
	private function get_current_round(): ?object
	{
		$round_id = isset($_GET['round_id']) ? absint($_GET['round_id']) : 0;

		if ($round_id <= 0) {
			wp_die('Chýba ID kola.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('rounds');

		$round = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $round_id)
		);

		if (!$round) {
			wp_die('Kolo neexistuje.');
		}

		return $round;
	}

	/**
	 * Get round by slug and phase.
	 *
	 * @param string $slug Slug.
	 * @param int    $phase_id Phase ID.
	 * @return object|null
	 */
	private function get_round_by_slug_and_phase(string $slug, int $phase_id): ?object
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('rounds');

		$round = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE slug = %s AND phase_id = %d LIMIT 1",
				$slug,
				$phase_id
			)
		);

		return $round ?: null;
	}
}