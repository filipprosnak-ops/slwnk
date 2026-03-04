# MAHL League Plugin – Coding Conventions

This file defines coding conventions for the MAHL League WordPress plugin.
All generated code must follow these rules to ensure consistency,
security, and maintainability.

---

# PHP Architecture

Use Object Oriented PHP.

Avoid procedural code except in the main plugin bootstrap file.

Recommended structure:

mahl-league.php
includes/
admin/
frontend/
services/
models/

---

# Naming Rules

All functions, hooks, and meta keys must use the prefix:

ml_

Examples:

ml_register_team_cpt
ml_match_home_score
ml_player_number

---

# Class Naming

Use PascalCase for class names.

Examples:

Plugin
TeamCPT
PlayerCPT
MatchCPT
StatsEngine
StandingsCalculator
PlayerStatsCalculator

---

# File Naming

All class files should use the pattern:

class-{name}.php

Examples:

class-plugin.php
class-team-cpt.php
class-player-cpt.php
class-match-cpt.php
class-stats-engine.php

---

# WordPress Hooks

Hooks must be registered inside classes.

Example:

add_action('init', [$this, 'register_cpt']);

Avoid global function hooks where possible.

---

# Security Rules

All admin actions must include:

Nonce verification:

check_admin_referer()

Capability checks:

current_user_can()

Input sanitization:

sanitize_text_field()
absint()
sanitize_email()

Output escaping:

esc_html()
esc_attr()
esc_url()

---

# Database Rules

Do NOT create custom database tables unless absolutely necessary.

Use WordPress native structures:

Custom Post Types
Taxonomies
Post Meta

---

# Performance Rules

Never calculate standings or statistics on page load.

Statistics must be recalculated only:

• when a match is saved
• when an admin triggers recalculation

Use caching where possible.

---

# Frontend Rules

All plugin styles must be scoped to the plugin wrapper:

<div class="ml-league">

Do not affect global theme styles.

---

# CSS Rules

Use CSS variables.

Example:

--ml-primary
--ml-accent
--ml-border
--ml-bg

Avoid global CSS resets.

---

# JavaScript

Use minimal JavaScript.

Allowed usage:

• Match editor UI
• Timeline event builder

Avoid heavy frameworks.

Use vanilla JS or lightweight modules.

---

# Code Quality

Follow WordPress Coding Standards (WPCS).

All classes must include PHPDoc documentation.

Example:

/**
 * Handles standings calculation.
 */
class StatsEngine {}

---

# Logging

Admin actions should log important events:

• match updates
• stats recalculations
• imports

Use a simple logging service or admin log CPT.

---

# Documentation

Every class must contain:

• PHPDoc description
• method documentation
• parameter types

