=== MAHL League ===
Contributors: mahl
Tags: sports, league, hockey, standings, statistics
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

MAHL League is a WordPress plugin for managing MAHL teams, players, matches, standings, stats, and related admin workflows.

== Description ==
MAHL League provides a production-focused toolkit for league administration:

* Custom post types for teams, players, and matches
* Taxonomies for seasons, competitions, and phases
* Admin match editor
* Secure CSV import/export for teams, players, and matches
* JSON backup/restore for plugin data
* Cached standings/statistics with recompute hooks
* Frontend shortcodes and templates for league views

== Installation ==
1. Upload the `mahl-league` folder to `/wp-content/plugins/`, or install via Plugins > Add New > Upload Plugin.
2. Activate **MAHL League** in WordPress admin.
3. Ensure the administrator role has `manage_ml_league` capability (granted automatically on activation).
4. Open **MAHL Liga** menu in wp-admin to manage league data.

== Usage ==
Shortcodes (use on any page/post):

* `[ml_teams]`
* `[ml_matches view="fixtures"]`
* `[ml_matches view="results"]`
* `[ml_standings]`
* `[ml_stats]`
* `[ml_playoffs]`

Auto pages (created/linked on activation):

* `/timy`
* `/zapasy`
* `/vysledky`
* `/tabulky`
* `/statistiky`
* `/play-off`

Pages are tracked in plugin option map and can be repaired from **MAHL Liga > Settings** using **Create/Repair Pages**.

== Import/Export and Backup/Restore ==
* **Import/Export (CSV):** available under **MAHL Liga > Import / Export** with dry-run support and validation.
* **Backup/Restore (JSON):** available under **MAHL Liga > Backup / Restore** for plugin data migration/recovery.
* All state-changing admin actions require capability checks and nonces.

== Requirements ==
* WordPress 6.0+
* PHP 8.1+

== Security Notes ==
* Admin tools are restricted to users with `manage_ml_league` capability.
* State-changing admin actions require nonce verification.

== Changelog ==
= 1.0.0 =
* Production release of MAHL League plugin scaffold.
* Includes admin UI, CSV import/export, backup/restore, cached stats engine, shortcodes, and frontend templates.
