# Project Context

Project: MAHL Manager WordPress Plugin

Goal:
Create a clean and professional WordPress plugin for managing amateur hockey leagues.

Important rules:
- Frontend pages should generate automatically
- Avoid shortcode-based architecture for core pages
- Follow WordPress coding standards
- Prefer modular architecture
- Treat the plugin as a CMS-style WordPress module
- All core content must be managed in the WordPress admin through custom post types
- Core functionality must not depend on Gutenberg blocks, shortcodes, or page builders
- Frontend output must be rendered through custom post type archive templates, single templates, and a plugin template loader
- Core frontend pages should exist automatically, similar to WooCommerce product pages
- No manual page creation should be required for seasons, teams, players, games, or standings

Main entities:
- Season
- Team
- Player
- Game
- Standings
- Player Statistics

The plugin should remain simple and maintainable.