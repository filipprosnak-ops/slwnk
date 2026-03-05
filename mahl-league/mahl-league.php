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


/**
 * Validate runtime requirements.
 *
 * @return true|string
 */
function ml_validate_requirements() {
	global $wp_version;
	if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
		return __( 'MAHL League requires PHP 8.1 or newer.', 'mahl-league' );
	}
	if ( version_compare( (string) $wp_version, '6.0', '<' ) ) {
		return __( 'MAHL League requires WordPress 6.0 or newer.', 'mahl-league' );
	}
	if ( ! file_exists( __DIR__ . '/includes/class-ml-autoloader.php' ) ) {
		return __( 'MAHL League bootstrap files are missing.', 'mahl-league' );
	}
	return true;
}

/**
 * Register fail-safe admin notice.
 *
 * @param string $message Notice text.
 *
 * @return void
 */
function ml_register_bootstrap_notice( string $message ): void {
	add_action(
		'admin_notices',
		static function () use ( $message ): void {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
		}
	);
}

$ml_requirements = ml_validate_requirements();
if ( true !== $ml_requirements ) {
	ml_register_bootstrap_notice( (string) $ml_requirements );
	return;
}

require_once __DIR__ . '/includes/class-ml-autoloader.php';
ML_Autoloader::register();

register_activation_hook(
	__FILE__,
	static function (): void {
		$requirements = ml_validate_requirements();
		if ( true !== $requirements ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html( (string) $requirements ) );
		}

		try {
			ML_Plugin::activate();
		} catch ( Throwable $throwable ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html( $throwable->getMessage() ) );
		}
	}
);

register_deactivation_hook( __FILE__, array( ML_Plugin::class, 'deactivate' ) );

try {
	ML_Plugin::get_instance()->init();
	define( 'ML_PLUGIN_READY', true );
} catch ( Throwable $throwable ) {
	ml_register_bootstrap_notice( sprintf( __( 'MAHL League initialization failed: %s', 'mahl-league' ), $throwable->getMessage() ) );
}
