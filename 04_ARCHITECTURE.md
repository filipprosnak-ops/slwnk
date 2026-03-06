# Plugin Architecture

Root:
mahl-manager/

Structure:

mahl-manager.php
includes/
post-types/
services/
admin/
frontend/
templates/
assets/

Custom post types:
mahl_season
mahl_phase
mahl_team
mahl_player
mahl_game

Services:
StandingsService
PlayerStatsService
GameService
TeamService
SeasonService
PhaseService

Competition hierarchy:
- Season is the top-level competition year
- Phase is a separate entity within a season
- Round is stored as a numeric game field
- Group/Bracket is stored as an optional game field