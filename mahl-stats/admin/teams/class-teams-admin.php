<?php
/**
 * Teams admin controller.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Teams_Admin
{
	/**
	 * Render teams admin page.
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
			$this->render_form($this->get_current_team());
			return;
		}

		$this->render_list();
	}

	/**
	 * Render teams list.
	 *
	 * @return void
	 */
	private function render_list(): void
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('teams');
		$teams = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC");

		include MAHL_STATS_PLUGIN_DIR . 'admin/teams/views/list.php';
	}

	/**
	 * Render add/edit form.
	 *
	 * @param object|null $team Team object.
	 * @return void
	 */
	private function render_form(?object $team = null): void
	{
		if ($team === null) {
			$team = (object) [
				'id'           => 0,
				'name'         => '',
				'slug'         => '',
				'logo_id'      => '',
				'city'         => '',
				'founded_year' => '',
			];
		}

		include MAHL_STATS_PLUGIN_DIR . 'admin/teams/views/form.php';
	}

	/**
	 * Handle team save.
	 *
	 * @return void
	 */
	public function handle_save(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_save_team');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('teams');

		$team_id      = isset($_POST['team_id']) ? absint($_POST['team_id']) : 0;
		$name         = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
		$slug         = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';
		$logo_id      = isset($_POST['logo_id']) ? absint($_POST['logo_id']) : 0;
		$city         = isset($_POST['city']) ? sanitize_text_field((string) $_POST['city']) : '';
		$founded_year = isset($_POST['founded_year']) ? absint($_POST['founded_year']) : 0;

		if ($name === '') {
			wp_die('Názov tímu je povinný.');
		}

		if ($slug === '') {
			$slug = sanitize_title($name);
		}

		$existing_slug = $this->get_team_by_slug($slug);

		if ($existing_slug && (int) $existing_slug->id !== $team_id) {
			wp_die('Slug tímu už existuje.');
		}

		$data = [
			'name'         => $name,
			'slug'         => $slug,
			'logo_id'      => $logo_id > 0 ? $logo_id : null,
			'city'         => $city !== '' ? $city : null,
			'founded_year' => $founded_year > 0 ? $founded_year : null,
		];

		$formats = [
			'%s',
			'%s',
			'%d',
			'%s',
			'%d',
		];

		if ($team_id > 0) {
			$result = $wpdb->update(
				$table,
				$data,
				['id' => $team_id],
				$formats,
				['%d']
			);

			if ($result === false) {
				wp_die('Tím sa nepodarilo upraviť.');
			}

			wp_safe_redirect(admin_url('admin.php?page=mahl-teams&message=updated'));
			exit;
		}

		$result = $wpdb->insert($table, $data, $formats);

		if ($result === false) {
			wp_die('Tím sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-teams&message=created'));
		exit;
	}

	/**
	 * Get current team.
	 *
	 * @return object|null
	 */
	private function get_current_team(): ?object
	{
		$team_id = isset($_GET['team_id']) ? absint($_GET['team_id']) : 0;

		if ($team_id <= 0) {
			wp_die('Chýba ID tímu.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('teams');

		$team = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $team_id)
		);

		if (!$team) {
			wp_die('Tím neexistuje.');
		}

		return $team;
	}

	/**
	 * Get team by slug.
	 *
	 * @param string $slug Team slug.
	 * @return object|null
	 */
	private function get_team_by_slug(string $slug): ?object
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('teams');

		$team = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s LIMIT 1", $slug)
		);

		return $team ?: null;
	}
}