
# MAHL League Plugin – ARCHITECTURE

This document defines the **technical architecture and data flow** of the MAHL League WordPress plugin.
It ensures that both human developers and AI coding agents understand how the system is structured.

This file works together with:

PLUGIN_SPEC.md → feature specification  
AGENTS.md → AI agent instructions  
TASKS.md → development phases  
CONVENTIONS.md → coding standards

---

# 1. High Level Architecture

The plugin follows a **modular service architecture**.

Layers:

1. WordPress Layer
2. Domain Layer
3. Services Layer
4. Admin Interface
5. Frontend Interface

Diagram:

WordPress  
↓  
Data Models (Teams / Players / Matches)  
↓  
Services (Stats Engine / Cache / Calculations)  
↓  
Frontend Rendering (Templates / Components)

---

# 2. Core Modules

## 2.1 Data Layer

Responsible for storing league data.

Uses WordPress native structures:

Custom Post Types
Taxonomies
Post Meta

### Custom Post Types

ml_team  
ml_player  
ml_match

### Taxonomies

ml_season  
ml_competition  
ml_phase

---

# 3. Domain Models

Domain models represent logical entities in the league.

## Team

Represents a hockey team.

Fields:

team_id  
team_name  
team_short  
team_logo  
team_colors

---

## Player

Represents a player belonging to a team.

Fields:

player_id  
player_name  
player_number  
player_position  
player_team  
player_birthdate

---

## Match

Represents a played or scheduled match.

Fields:

match_id  
season  
competition  
phase  
datetime  
home_team  
away_team  
venue  
status

Scores:

home_score  
away_score

Additional data:

period_scores  
roster_home  
roster_away  
timeline_events

---

# 4. Service Layer

Services contain the business logic.

## StatsEngine

Main orchestrator for statistics.

Responsibilities:

calculate standings  
calculate player stats  
calculate team stats

Triggers:

match saved  
admin recompute

---

## StandingsCalculator

Computes league table.

Input:

list of matches

Output:

table rows:

team  
games played  
wins  
draws  
losses  
goals for  
goals against  
points

---

## PlayerStatsCalculator

Aggregates player performance across matches.

Calculates:

games played  
goals  
assists  
points  
penalty minutes

---

## TeamStatsCalculator

Aggregates team totals.

Calculates:

total goals  
total assists  
total penalties  
active players

---

## Cache Service

Stores computed data.

Cache storage:

WordPress options  
or term meta

Purpose:

avoid heavy calculations during page loads

---

# 5. Admin Interface

Admin interface allows league administrators to manage the system.

Main menu:

MAHL Liga

Admin screens:

Dashboard  
Teams  
Players  
Matches  
Standings  
Statistics  
Playoffs  
Import / Export  
Settings

---

# 6. Match Editor Architecture

Match Editor is the most complex admin screen.

Sections:

Basic match information  
Teams selection  
Score input  
Roster editor  
Timeline editor

Workflow:

admin fills match  
↓  
data validated  
↓  
match saved  
↓  
StatsEngine recalculates standings  
↓  
cache updated

---

# 7. Frontend Architecture

Frontend pages are rendered through plugin templates.

Pages:

/teams  
/matches  
/results  
/standings  
/stats  
/playoffs

Each page uses:

data query  
template rendering  
component styling

---

# 8. Component System

Frontend UI is built from reusable components.

Components:

match-card  
standings-table  
timeline  
team-card  
player-card  
stats-card

All components are styled inside:

.ml-league

This prevents conflicts with theme styles.

---

# 9. Data Flow Example

Example: match is saved

Admin saves match  
↓  
WordPress triggers save_post  
↓  
Plugin validates data  
↓  
StatsEngine triggered  
↓  
StandingsCalculator runs  
↓  
PlayerStatsCalculator runs  
↓  
TeamStatsCalculator runs  
↓  
Cache updated  
↓  
Frontend pages read cached data

---

# 10. Performance Strategy

Heavy operations must NOT run during page load.

Calculations only run when:

match saved  
admin triggers recalculation

Frontend pages read cached results.

---

# 11. Security Model

Security is enforced in every layer.

Admin actions require:

nonce verification  
capability checks

Data safety:

input sanitization  
output escaping

---

# 12. Extensibility

Architecture allows future features:

advanced goalie stats  
multi-league support  
API integrations  
mobile apps

New services can be added inside:

services/

---

# 13. Plugin Lifecycle

Activation:

register CPT  
register taxonomies  
flush rewrite rules

Runtime:

admin manages data  
services compute statistics  
frontend displays results

Uninstall:

remove plugin options  
clear caches

---

# 14. Architectural Goals

This architecture ensures:

scalability  
maintainability  
security  
performance

The plugin must remain compatible with any WordPress theme.
