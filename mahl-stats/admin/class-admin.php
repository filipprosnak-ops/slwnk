<?php
/**
 * Admin bootstrap class.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Admin
{
	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private string $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_name Plugin slug.
	 * @param string $version Plugin version.
	 */
	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public function register_menu(): void
	{
		$menu = new MAHL_Stats_Admin_Menu();
		$menu->register();
	}
}