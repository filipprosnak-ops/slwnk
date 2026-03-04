<?php
/**
 * Plugin Name: MAHL League
 * Description: League management plugin for MAHL competitions, teams, players, and matches.
 * Version: 0.1.0
 * Author: MAHL
 * Text Domain: mahl-league
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-ml-autoloader.php';

ML_Autoloader::register();

register_activation_hook( __FILE__, array( ML_Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( ML_Plugin::class, 'deactivate' ) );

ML_Plugin::get_instance()->init();
