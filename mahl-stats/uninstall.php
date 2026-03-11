<?php
/**
 * Uninstall MAHL STATS plugin.
 *
 * @package MAHL_Stats
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

/*
 * Intentionally conservative.
 * For v1.0 we do not delete data automatically.
 * Cleanup can be added later via explicit setting.
 */