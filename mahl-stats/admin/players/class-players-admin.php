<?php
/**
 * Players admin controller.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Players_Admin
{
	/**
	 * Render players admin page.
	 *
	 * @return void
	 */
	public function render(): void
	{
		$action = isset($_GET['action']) ? sanitize_key((string) $_GET['action']) : 'list';

		if ($action === 'add') {
			$this->render_form();
			return;
		}

		if ($action === 'edit') {
			$this->render_form($this->get_current_player());
			return;
		}

		$this->render_list();
	}

	/**
	 * Render players list.
	 *
	 * @return void
	 */
	private function render_list(): void
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('players');
		$players = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC");

		include MAHL_STATS_PLUGIN_DIR . 'admin/players/views/list.php';
	}

	/**
	 * Render add/edit form.
	 *
	 * @param object|null $player Player object.
	 * @return void
	 */
	private function render_form(?object $player = null): void
	{
		if ($player === null) {
			$player = (object) [
				'id'               => 0,
				'first_name'       => '',
				'last_name'        => '',
				'slug'             => '',
				'birth_date'       => '',
				'default_position' => 'F',
				'shoots'           => 'L',
			];
		}

		include MAHL_STATS_PLUGIN_DIR . 'admin/players/views/form.php';
	}

	/**
	 * Handle player save.
	 *
	 * @return void
	 */
	public function handle_save(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_save_player');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('players');

		$player_id        = isset($_POST['player_id']) ? absint($_POST['player_id']) : 0;
		$first_name       = isset($_POST['first_name']) ? sanitize_text_field((string) $_POST['first_name']) : '';
		$last_name        = isset($_POST['last_name']) ? sanitize_text_field((string) $_POST['last_name']) : '';
		$slug             = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';
		$birth_date       = isset($_POST['birth_date']) ? sanitize_text_field((string) $_POST['birth_date']) : '';
		$default_position = isset($_POST['default_position']) ? strtoupper(trim((string) $_POST['default_position'])) : 'F';
		$shoots           = isset($_POST['shoots']) ? strtoupper(trim((string) $_POST['shoots'])) : 'L';

		if ($first_name === '' || $last_name === '') {
			wp_die('Meno a priezvisko hráča sú povinné.');
		}

		if ($slug === '') {
			$slug = sanitize_title(trim($first_name . ' ' . $last_name));
		}

		$allowed_positions = ['G', 'D', 'F'];
		if (!in_array($default_position, $allowed_positions, true)) {
			$default_position = 'F';
		}

		$allowed_shoots = ['L', 'R'];
		if (!in_array($shoots, $allowed_shoots, true)) {
			$shoots = 'L';
		}

		$existing_slug = $this->get_player_by_slug($slug);

		if ($existing_slug && (int) $existing_slug->id !== $player_id) {
			wp_die('Slug hráča už existuje.');
		}

		$data = [
			'first_name'       => $first_name,
			'last_name'        => $last_name,
			'slug'             => $slug,
			'birth_date'       => $birth_date !== '' ? $birth_date : null,
			'default_position' => $default_position,
			'shoots'           => $shoots,
		];

		$formats = [
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		];

		if ($player_id > 0) {
			$result = $wpdb->update(
				$table,
				$data,
				['id' => $player_id],
				$formats,
				['%d']
			);

			if ($result === false) {
				wp_die('Hráča sa nepodarilo upraviť.');
			}

			wp_safe_redirect(admin_url('admin.php?page=mahl-players&message=updated'));
			exit;
		}

		$result = $wpdb->insert($table, $data, $formats);

		if ($result === false) {
			wp_die('Hráča sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-players&message=created'));
		exit;
	}

	/**
	 * Get current player.
	 *
	 * @return object|null
	 */
	private function get_current_player(): ?object
	{
		$player_id = isset($_GET['player_id']) ? absint($_GET['player_id']) : 0;

		if ($player_id <= 0) {
			wp_die('Chýba ID hráča.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('players');

		$player = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $player_id)
		);

		if (!$player) {
			wp_die('Hráč neexistuje.');
		}

		if (!property_exists($player, 'shoots') || $player->shoots === null || $player->shoots === '') {
			$player->shoots = 'L';
		}

		if (!property_exists($player, 'default_position') || $player->default_position === null || $player->default_position === '') {
			$player->default_position = 'F';
		}

		return $player;
	}

	/**
	 * Get player by slug.
	 *
	 * @param string $slug Player slug.
	 * @return object|null
	 */
	private function get_player_by_slug(string $slug): ?object
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('players');

		$player = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s LIMIT 1", $slug)
		);

		return $player ?: null;
	}
}