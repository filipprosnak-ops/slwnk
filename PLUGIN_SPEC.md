# MAHL Manager – Plugin Specification

Purpose:
WordPress plugin for managing amateur hockey leagues.

Core goals:
- Manage seasons, phases, teams, players, and games
- Automatically generate frontend pages
- Provide standings and player statistics
- Maintain simple architecture similar to WooCommerce content behavior

Version 1 features:
- Season management
- Phase management
- Team management
- Player management
- Game management
- Automatic standings calculation
- Basic player statistics

Competition hierarchy:
- Season is the main competition year, for example MAHL 2026/2027
- Phase is a separate competition structure within a season, for example Pre Season, Základná časť, Nadstavba, future Playoff
- Round is stored as a numeric field on each game
- Group or bracket is stored as an optional field on each game

Game organization requirements:
- Each game must belong to one season
- Each game must belong to one phase
- Each game must support a round number
- Each game may support an optional group or bracket key
- Standings must later be calculable by season, by phase, and optionally by group or bracket

Non-goals for V1:
- Payment systems
- User registrations
- Public submissions
- Advanced playoff engines

Frontend requirements:
- Automatic archive pages
- Automatic single pages
- No shortcode dependency for core pages