<?php
/**
 * Plugin Name: MAHL STATS
 * Plugin URI: https://mahliga.sk
 * Description: Štatistický systém pre MAHL ligu vo WordPresse.
 * Version: 1.0.0
 * Author: MAHL
 * Author URI: https://mahliga.sk
 * Text Domain: mahl-stats
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 8.0
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

define('MAHL_STATS_VERSION', '1.0.0');
define('MAHL_STATS_DB_VERSION', '1.0.1');
define('MAHL_STATS_PLUGIN_FILE', __FILE__);
define('MAHL_STATS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MAHL_STATS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MAHL_STATS_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once MAHL_STATS_PLUGIN_DIR . 'includes/class-loader.php';
require_once MAHL_STATS_PLUGIN_DIR . 'includes/class-activator.php';
require_once MAHL_STATS_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once MAHL_STATS_PLUGIN_DIR . 'includes/class-plugin.php';

require_once MAHL_STATS_PLUGIN_DIR . 'src/Database/class-db.php';
require_once MAHL_STATS_PLUGIN_DIR . 'src/Database/class-schema.php';
require_once MAHL_STATS_PLUGIN_DIR . 'src/Database/class-migrations.php';

/**
 * Runs on plugin activation.
 *
 * @return void
 */
function mahl_stats_activate(): void
{
	MAHL_Stats_Activator::activate();
}
register_activation_hook(__FILE__, 'mahl_stats_activate');

/**
 * Runs on plugin deactivation.
 *
 * @return void
 */
function mahl_stats_deactivate(): void
{
	MAHL_Stats_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'mahl_stats_deactivate');

/**
 * Bootstrap the plugin.
 *
 * @return MAHL_Stats_Plugin
 */
function mahl_stats(): MAHL_Stats_Plugin
{
	static $plugin = null;

	if ($plugin === null) {
		$plugin = new MAHL_Stats_Plugin();
		$plugin->run();
	}

	return $plugin;
}

mahl_stats();