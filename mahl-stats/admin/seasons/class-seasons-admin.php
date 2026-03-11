<?php
/**
 * Seasons admin controller.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Seasons_Admin
{
	/**
	 * Render seasons admin page.
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
				$this->render_form($this->get_current_season());
				break;

			default:
				$this->render_list();
				break;
		}
	}

	/**
	 * Render seasons list page.
	 *
	 * @return void
	 */
	private function render_list(): void
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('seasons');
		$seasons = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC");

		include MAHL_STATS_PLUGIN_DIR . 'admin/seasons/views/list.php';
	}

	/**
	 * Render add/edit form.
	 *
	 * @param object|null $season Season object.
	 * @return void
	 */
	private function render_form(?object $season = null): void
	{
		if ($season === null) {
			$season = (object) [
				'id'         => 0,
				'name'       => '',
				'slug'       => '',
				'start_date' => '',
				'end_date'   => '',
				'status'     => 'draft',
			];
		}

		include MAHL_STATS_PLUGIN_DIR . 'admin/seasons/views/form.php';
	}

	/**
	 * Handle add/edit save via admin-post.php
	 *
	 * @return void
	 */
	public function handle_save(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_save_season');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('seasons');

		$season_id  = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
		$name       = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
		$slug       = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';
		$start_date = isset($_POST['start_date']) ? sanitize_text_field((string) $_POST['start_date']) : '';
		$end_date   = isset($_POST['end_date']) ? sanitize_text_field((string) $_POST['end_date']) : '';
		$status     = isset($_POST['status']) ? sanitize_key((string) $_POST['status']) : 'draft';

		if ($name === '') {
			wp_die('Názov sezóny je povinný.');
		}

		if ($slug === '') {
			$slug = sanitize_title($name);
		}

		$allowed_statuses = ['draft', 'active', 'archived'];

		if (!in_array($status, $allowed_statuses, true)) {
			$status = 'draft';
		}

		$existing_slug = $this->get_season_by_slug($slug);

		if ($existing_slug && (int) $existing_slug->id !== $season_id) {
			wp_die('Slug už existuje. Zadaj iný slug.');
		}

		$data = [
			'name'       => $name,
			'slug'       => $slug,
			'start_date' => $start_date !== '' ? $start_date : null,
			'end_date'   => $end_date !== '' ? $end_date : null,
			'status'     => $status,
		];

		$formats = [
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		];

		if ($season_id > 0) {
			$result = $wpdb->update(
				$table,
				$data,
				['id' => $season_id],
				$formats,
				['%d']
			);

			if ($result === false) {
				wp_die('Sezónu sa nepodarilo upraviť.');
			}

			wp_safe_redirect(admin_url('admin.php?page=mahl-seasons&message=updated'));
			exit;
		}

		$result = $wpdb->insert($table, $data, $formats);

		if ($result === false) {
			wp_die('Sezónu sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-seasons&message=created'));
		exit;
	}

	/**
	 * Handle archive season action.
	 *
	 * @return void
	 */
	public function handle_archive(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		$season_id = isset($_GET['season_id']) ? absint($_GET['season_id']) : 0;

		if ($season_id <= 0) {
			wp_die('Chýba ID sezóny.');
		}

		check_admin_referer('mahl_archive_season_' . $season_id);

		global $wpdb;

		$table = MAHL_Stats_DB::table('seasons');

		$result = $wpdb->update(
			$table,
			['status' => 'archived'],
			['id' => $season_id],
			['%s'],
			['%d']
		);

		if ($result === false) {
			wp_die('Sezónu sa nepodarilo archivovať.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-seasons&message=archived'));
		exit;
	}

	/**
	 * Get current season by query var.
	 *
	 * @return object|null
	 */
	private function get_current_season(): ?object
	{
		$season_id = isset($_GET['season_id']) ? absint($_GET['season_id']) : 0;

		if ($season_id <= 0) {
			wp_die('Chýba ID sezóny.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('seasons');

		$season = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $season_id)
		);

		if (!$season) {
			wp_die('Sezóna neexistuje.');
		}

		return $season;
	}

	/**
	 * Get season by slug.
	 *
	 * @param string $slug Season slug.
	 * @return object|null
	 */
	private function get_season_by_slug(string $slug): ?object
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('seasons');

		$season = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s LIMIT 1", $slug)
		);

		return $season ?: null;
	}
}