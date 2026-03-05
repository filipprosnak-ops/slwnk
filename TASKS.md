# TASKS.md

## Phase 1 – Plugin Skeleton

Create folder structure:

mahl-league/
mahl-league.php
includes/
admin/
frontend/
templates/
assets/

Register:

Custom Post Types:
- teams
- players
- matches

Taxonomies:
- season
- competition
- phase

---

## Phase 2 – Admin Interface

Create admin menu:

MAHL Liga

Screens:

Dashboard
Teams
Players
Matches
Standings
Statistics
Playoffs
Import / Export
Settings

Implement Match Editor UI.

---

## Phase 3 – Stats Engine

Implement:

Standings calculation
Player statistics
Team statistics

Add caching.

---

## Phase 4 – Frontend

Create templates:

matches
results
standings
stats
teams
player detail
match detail

---

## Phase 5 – CSS

Create UI components:

match cards
standings table
timeline
player cards
team statistics cards

---

## Phase 6 – Security

Add:

nonce checks
capabilities
sanitization
escaping

---

## Phase 7 – Import / Export

CSV import:

players
teams
matches
