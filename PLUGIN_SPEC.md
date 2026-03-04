# MAHL League – Ultimate Production Plugin Spec (WordPress)
**Goal:** Generate a **production‑grade WordPress plugin** that replicates the **frontend UX/system** of *podunajskahokejovaliga.sk* (PHL) while using **MAHL branding**, and provides a **user‑friendly admin backend** so league staff can manage teams, players, matches, standings, stats, and playoffs with minimal training.

> This is a **single source of truth** spec for an AI/Codex agent. Do not improvise beyond this spec unless explicitly marked “Optional”.

---

## 1) Product Scope

### 1.1 Must‑Have Features (MVP+)
1. **Competitions** (e.g., MAHL, Cup) and **Seasons**
2. **Teams** with logos, abbreviations, colors, venue
3. **Players** with number, position, age, team, status, exception flag, captaincy
4. **Matches**
   - scheduled/played/canceled
   - venue, referees, round, stage label (e.g., Final, SF1)
   - final score + per-period scores
   - rosters (home/away) + per-player match stats
   - match events timeline (goals/assists, penalties, timeouts, goalie stats)
5. **Standings (Table)** computed from matches for a season+competition
6. **Stats**
   - top goals
   - top assists
   - points (goals+assists)
   - team totals + per-player totals
7. **Playoffs**
   - phase-based grouping (Quarterfinal/Semifinal/Final/3rd place) with match cards
   - bracket view for KO phases (MVP: grouped rounds; v2: bracket rendering)
8. **Frontend pages** mirroring PHL info architecture:
   - Teams, Matches (Fixtures/Results), Standings, Playoffs, Stats, About, League Management
9. **User-friendly Admin UI**
   - single “Match Editor” screen to input everything (no raw JSON editing)
10. **Safety & Reliability**
   - secure admin actions
   - audit trail
   - cache and recomputation strategy
   - import/export
   - upgrades/migrations

### 1.2 Non-Goals
- Ticketing, payments, shop, membership subscriptions
- Live game clock integrations
- Automatic data scraping from third-party sites

---

## 2) Technical Constraints & Principles

### 2.1 Platform
- WordPress: 6.x
- PHP: 8.1+ (target 8.2)
- MySQL/MariaDB (standard WP)
- Must work with Blocksy (or any theme). Plugin provides its own templates/styles only for league pages.

### 2.2 Architecture Principles
- **WP-native storage**: custom post types + taxonomies + post meta (avoid custom DB tables unless necessary).
- **Compute-on-change**: standings/stats recomputed **on match save** or via admin “Recompute” action; never compute heavy stats on each page load.
- **Template isolation**: plugin uses template loader; does not require child theme.
- **CSS isolation**: only apply styles under `.ml-league` scope.
- **Security first**: nonce + capability checks + strict sanitization and escaping.
- **Maintainability**: modular files, PSR-4 style namespacing, CI linting/testing.
- **Observability**: admin logs, debug mode, clear error messages.

---

## 3) Plugin Identity

- Plugin name: **MAHL League**
- Slug: `mahl-league`
- Textdomain: `mahl-league`
- Prefix for functions/meta keys: `ml_`
- Namespace: `MAHL\League`

---

## 4) Folder Structure (Production)

```
mahl-league/
  mahl-league.php                # bootstrap
  composer.json                  # autoload + tooling (optional but recommended)
  uninstall.php
  readme.txt
  LICENSE

  src/
    Plugin.php                   # main orchestrator
    Setup/Activation.php
    Setup/Deactivation.php
    Setup/Migrations.php
    Setup/Capabilities.php

    Data/
      PostTypes/TeamCPT.php
      PostTypes/PlayerCPT.php
      PostTypes/MatchCPT.php
      Taxonomies/SeasonTAX.php
      Taxonomies/CompetitionTAX.php
      Taxonomies/PhaseTAX.php
      Meta/MetaKeys.php
      Meta/ValidationRules.php

    Admin/
      Menu.php
      Screens/DashboardScreen.php
      Screens/MatchEditorScreen.php
      Screens/TeamEditorScreen.php
      Screens/PlayerEditorScreen.php
      Screens/StandingsScreen.php
      Screens/StatsScreen.php
      Screens/PlayoffsScreen.php
      Screens/ImportExportScreen.php
      Screens/SettingsScreen.php
      UI/Components.php
      UI/Notices.php
      Audit/AuditLog.php

    Domain/
      Models/MatchEvent.php
      Models/RosterEntry.php
      Models/PeriodScore.php
      Models/StandingsRow.php
      Models/PlayerTotals.php
      Models/TeamTotals.php

    Services/
      StatsEngine.php
      StandingsCalculator.php
      PlayerStatsCalculator.php
      TeamStatsCalculator.php
      PlayoffBuilder.php
      Cache.php
      Formatter.php
      Slugger.php
      ImportExport.php

    REST/
      Routes.php
      Controllers/TeamsController.php
      Controllers/PlayersController.php
      Controllers/MatchesController.php
      Controllers/StandingsController.php
      Controllers/StatsController.php

    Frontend/
      Router.php
      TemplateLoader.php
      Shortcodes.php
      Blocks.php                  # optional (Gutenberg blocks)
      Assets.php
      Hooks.php

  templates/
    league-shell.php              # wraps league pages with .ml-league
    archive-ml_team.php
    single-ml_team.php
    archive-ml_player.php
    single-ml_player.php
    archive-ml_match.php
    single-ml_match.php
    page-ml-fixtures.php
    page-ml-results.php
    page-ml-standings.php
    page-ml-stats.php
    page-ml-playoffs.php

  assets/
    css/league.css
    js/admin-match-editor.js
    js/public.js
    img/

  tests/
    phpunit.xml
    Unit/StatsEngineTest.php
    Unit/StandingsCalculatorTest.php
    Unit/PlayerStatsCalculatorTest.php
    Unit/PlayoffBuilderTest.php

  .github/workflows/ci.yml        # lint + tests
  phpcs.xml
  phpstan.neon
```

---

## 5) Data Model (WP-native)

### 5.1 Custom Post Types

#### 5.1.1 Team
- post_type: `ml_team`
- supports: `title`, `thumbnail`, `editor`
- show_in_rest: true
- has_archive: true
- rewrite slug: `tim` (PHL-like: `/tim/{id}-{slug}`)
- admin menu: under “MAHL Liga”

**Meta keys (Team)**
- `ml_team_short` (string, 2–5 chars, uppercase)
- `ml_team_colors` (object: `{primary, secondary}` as hex)
- `ml_team_home_venue` (string)
- `ml_team_city` (string, optional)
- `ml_team_contact_name` (string, optional)
- `ml_team_contact_email` (email, optional)
- `ml_team_contact_phone` (string, optional)

#### 5.1.2 Player
- post_type: `ml_player`
- supports: `title`, `thumbnail`
- show_in_rest: true
- has_archive: true
- rewrite slug: `hrac`

**Meta keys (Player)**
- `ml_player_number` (int, 0–99)
- `ml_player_position` (enum: `G` | `D` | `F`)
- `ml_player_birthdate` (date `YYYY-MM-DD`, optional)
- `ml_player_shoots` (enum: `L` | `R` | `N`, optional)
- `ml_player_team_id` (post ID of `ml_team`, required)
- `ml_player_status` (enum: `active` | `inactive`)
- `ml_player_exception` (bool)
- `ml_player_role` (enum: `none` | `C` | `A`)

#### 5.1.3 Match
- post_type: `ml_match`
- supports: `title` (auto), `custom-fields`
- show_in_rest: true
- has_archive: true
- rewrite slug: `zapas`

**Taxonomy assignments (Match)**
- `ml_season` (required)
- `ml_competition` (required)
- `ml_phase` (required) — e.g. “ZČ”, “Play-off”

**Meta keys (Match – core)**
- `ml_match_datetime` (datetime `YYYY-MM-DD HH:MM` in site tz)
- `ml_match_venue` (string)
- `ml_match_round_label` (string, e.g., “Kolo 1”)
- `ml_match_stage_label` (string, e.g., “Semifinále 1”, “Finále”, “O tretie miesto”)
- `ml_match_status` (enum: `scheduled` | `played` | `canceled`)
- `ml_match_home_team_id` (post ID `ml_team`)
- `ml_match_away_team_id` (post ID `ml_team`)
- `ml_match_referees` (array of strings OR optional CPT later)
- `ml_match_note` (string, optional)

**Meta keys (Match – scores)**
- `ml_match_home_score` (int)
- `ml_match_away_score` (int)
- `ml_match_period_scores` (array of PeriodScore objects; see §6)

**Meta keys (Match – rosters & events)**
- `ml_match_roster_home` (array of RosterEntry objects; see §6)
- `ml_match_roster_away` (array of RosterEntry objects; see §6)
- `ml_match_events` (array of MatchEvent objects; see §6)

---

### 5.2 Taxonomies
All taxonomies: `show_in_rest: true`, `public: true`.

1. `ml_season` (hierarchical)
   - examples: “2026/2027”, “2025/2026”
2. `ml_competition` (hierarchical)
   - examples: “MAHL”, “MAHL Cup”
3. `ml_phase` (hierarchical)
   - examples: “Základná časť”, “Play-off”

---

## 6) JSON Schemas (stored as post meta)

### 6.1 PeriodScore (array item for `ml_match_period_scores`)
```json
{
  "period": "P1",
  "home": 1,
  "away": 0
}
```
- `period` enum: `P1` `P2` `P3` `OT` `SO`
- `home` int >=0, `away` int >=0

### 6.2 RosterEntry (array item for roster home/away)
```json
{
  "player_id": 123,
  "position": "F",
  "line": 1,
  "goals": 0,
  "assists": 1,
  "pim": 2,
  "shots": 3,
  "plus_minus": 1,
  "goalie": {
    "shots_against": 28,
    "saves": 26
  }
}
```
Rules:
- `player_id` required, must exist and be assigned to team (warn if mismatch)
- `position` enum: `G`|`D`|`F`
- `goalie` object only if position `G`
- compute `sv_pct = saves/shots_against` at render time (not stored) or store as derived (optional)

### 6.3 MatchEvent (timeline item for `ml_match_events`)
```json
{
  "period": "P2",
  "time": "07:38",
  "type": "goal",
  "team": "home",
  "scorer_id": 123,
  "assist_1_id": 234,
  "assist_2_id": null,
  "penalty": null,
  "score_home": 2,
  "score_away": 1,
  "note": ""
}
```
Event types:
- goal: scorer_id required; assist ids optional
- penalty:
```json
{
  "period":"P1",
  "time":"11:20",
  "type":"penalty",
  "team":"away",
  "penalty": {
    "player_id": 345,
    "minutes": 2,
    "code": "MIN",
    "label": "Hrubosť"
  },
  "score_home": 0,
  "score_away": 0
}
```
- timeout:
```json
{"period":"P3","time":"15:00","type":"timeout","team":"home","score_home":3,"score_away":2}
```

Validation:
- time format `MM:SS`
- score_* ints >=0
- period enum `P1,P2,P3,OT,SO`

---

## 7) Standings & Stats Rules

### 7.1 Point System (Configurable)
Settings per competition or global:
- win_points (default **2**)
- draw_points (default **1**)
- loss_points (default **0**)
Optional v2:
- ot_win_points, ot_loss_points, so_win_points, so_loss_points

### 7.2 Standings Row Fields
- GP (games played)
- W, D, L
- GF (goals for), GA (goals against), GD (difference)
- PTS
- tie-breaker ordering:
  1) PTS
  2) GD
  3) GF
  4) head-to-head (optional v2)

### 7.3 Player Totals
Per season+competition:
- GP, G, A, P (G+A), PIM
Optional v2:
- +/- , SOG, GWG

### 7.4 Team Totals
Per season+competition:
- active players count
- total goals, assists, PIM, GP

### 7.5 Recompute Strategy
Triggered by:
- match saved with status `played`
- match changed between `played` and `scheduled/canceled`
- manual “Recompute” admin action
- nightly WP-Cron safety recompute (optional)

Storage:
- `term_meta` for season term id + competition term id keys:
  - `ml_cache_standings_{competitionId}`
  - `ml_cache_stats_{competitionId}`
- plus a `ml_cache_version` (increment on invalidation)

Cache format:
- JSON with schema version
- include generated timestamp
- include inputs hash (match IDs + last modified timestamps) for safety

---

## 8) Admin UX (User Friendly)

### 8.1 Admin Menu (“MAHL Liga”)
- Dashboard
- Matches
- Teams
- Players
- Standings (Preview + Recompute)
- Stats (Preview + Recompute)
- Playoffs (Builder + Mapping)
- Import/Export
- Settings
- Audit Log

### 8.2 Match Editor Screen (single-screen workflow)
**Top bar**
- Save Draft
- Publish/Update
- “Save & Recompute” (primary)

**Section A: Basics**
- Season (dropdown)
- Competition (dropdown)
- Phase (dropdown)
- Stage label (text)
- Round label (text)
- Date & time (datetime picker)
- Venue (text)
- Referees (repeatable text)
- Status (scheduled/played/canceled)

**Section B: Teams**
- Home team (select + logo preview)
- Away team (select + logo preview)

**Section C: Scores**
- Final score (home/away)
- Period scores grid (P1, P2, P3, OT, SO)

**Section D: Rosters**
Two tabs: Home / Away
- Add player row (select player; filter by selected team)
- Columns:
  - Position (G/D/F)
  - Line (1–4, optional)
  - G, A, PIM (required)
  - Optional advanced: SOG, +/- (toggle)
  - For goalies: shots against + saves
- Bulk add: “Add all active team players” button (populates list)

**Section E: Timeline**
- “Add event” panel:
  - period, time
  - type: goal/penalty/timeout
  - team: home/away
  - for goal: scorer + assists
  - for penalty: player + minutes + label
  - auto-updates score snapshot if goal is added (optional: user override)
- Events list sortable (drag & drop)
- Validate chronological order; warn but allow override

**Section F: Validation / Warnings**
- team mismatch (player not in team)
- totals mismatch (period sums != final score)
- missing goalie stats if goalie present

### 8.3 Team Editor Screen
- Name, logo, abbreviation, colors, venue, contacts
- “Roster” panel listing players assigned (quick links)

### 8.4 Player Editor Screen
- Name, photo, number, position, birthdate, shoots, team, status, exception, role (C/A)
- “Season totals” (read-only) from cache

### 8.5 Audit Log
Record:
- who edited match/team/player
- what changed (diff summary)
- timestamp
Storage: custom post type `ml_audit` or option array with capped size (prefer CPT).

---

## 9) Frontend Pages & Routes (PHL-like IA)

Plugin creates pages on activation (if missing) with these slugs and assigns templates:
- `/timy` -> Teams list
- `/zapasy` -> Fixtures (upcoming)
- `/zapasy/vysledky` -> Results (played)
- `/tabulky` -> Standings
- `/statistiky` -> Stats
- `/play-off` -> Playoffs
Optional:
- `/o-lige`, `/vedenie` as regular WP pages (plugin provides styling only)

### 9.1 Fixtures/Results UX Requirements
- group matches by day header (localized day name + date)
- match card layout:
  - time
  - badges: status + stage label
  - home team chip (logo+name+abbr)
  - center score (or “– : –” if upcoming)
  - away team chip
  - venue line
- clicking match opens match detail

### 9.2 Standings UX Requirements
- table with columns: #, Team, GP, W, D, L, GF:GA, PTS
- team cell includes logo + name
- responsive: horizontal scroll on mobile + sticky header optional

### 9.3 Team Detail UX Requirements
- header: logo, name, season selector
- “Team Stats” cards (active players, goals, assists, PIM, games)
- tabs: Players / Matches
- players table: #, name, status, pos, GP, G, A, P
- matches list: cards like fixtures/results but filtered to team

### 9.4 Player Detail UX Requirements
- header: photo, name, “Position • #Number”, age, team link
- stat cards: P, G, A, PIM, GP
- season accordion: show per season+competition totals
- match list: each match row shows micro-stats (G/A/PIM) + “Match detail” link

### 9.5 Match Detail UX Requirements
Sections in order:
1) teams + score + status
2) metadata: date/time, round, venue, referees
3) period scores
4) rosters (group by position: goalies/defense/forwards) with per-player match stats
5) timeline grouped by period with icons and score snapshots

Accessibility:
- all tables have proper headers
- badges have aria-labels
- color contrast meets WCAG AA

---

## 10) CSS & Branding (MAHL look, PHL layout)

### 10.1 Scoping
All frontend league templates must include wrapper:
```html
<div class="ml-league ml-theme-mahl">
  ...
</div>
```

### 10.2 CSS Variables
Define on `.ml-theme-mahl`:
- `--ml-bg`
- `--ml-card`
- `--ml-text`
- `--ml-muted`
- `--ml-border`
- `--ml-accent`
- `--ml-accent-2`
- `--ml-radius` (12px)
- `--ml-gap` (12px)
- `--ml-shadow` (subtle)

### 10.3 Components (classes)
- `.ml-nav-tabs`
- `.ml-day-group`, `.ml-day-title`
- `.ml-match-card`, `.ml-match-time`, `.ml-match-badges`, `.ml-match-teams`, `.ml-match-venue`
- `.ml-team-chip`, `.ml-team-logo`, `.ml-team-name`, `.ml-team-abbr`
- `.ml-score`
- `.ml-standings-table`
- `.ml-stat-cards`, `.ml-stat-card`
- `.ml-tabs`
- `.ml-players-table`
- `.ml-accordion`
- `.ml-timeline`, `.ml-event`, `.ml-event--goal`, `.ml-event--penalty`, `.ml-event--timeout`
- `.ml-pill` (badges)

### 10.4 Performance
- CSS in single file `assets/css/league.css`
- no external frameworks; minimal JS

---

## 11) REST API (Optional but recommended)
Expose read-only endpoints for future apps/overlays.

Base: `/wp-json/mahl-league/v1`

- `GET /teams?season=...&competition=...`
- `GET /players?team_id=...&season=...`
- `GET /matches?status=upcoming|played&season=...`
- `GET /standings?season=...&competition=...`
- `GET /stats?season=...&competition=...`

Security: public read-only; admin write endpoints not required.

---

## 12) Import/Export

### 12.1 CSV Import
- Teams: name, abbr, venue, colors
- Players: name, number, position, birthdate, team_abbr, status, exception, role
- Matches: season, competition, phase, stage_label, round_label, datetime, venue, home_abbr, away_abbr, status, home_score, away_score, period_scores_json (optional)

### 12.2 Export
- Standings (CSV)
- Player totals (CSV)
- Matches list (CSV)

---

## 13) Security Requirements (Production Checklist)

Must implement:
- `ABSPATH` guard in all PHP files
- nonces for all admin POST actions
- capability checks:
  - `manage_ml_league` for settings/recompute/import
  - `edit_ml_match`, `edit_ml_team`, `edit_ml_player` mapping for CPT edits
- sanitize all inputs:
  - strings: `sanitize_text_field`
  - ints: `absint`
  - arrays: recursive sanitize
  - emails: `sanitize_email`
- escape all outputs in templates:
  - HTML text: `esc_html`
  - attrs: `esc_attr`
  - URLs: `esc_url`
- prevent stored XSS in notes/referees/labels
- no direct SQL unless prepared via `$wpdb->prepare`
- do not allow file uploads except WP media for logos/photos (use WP media UI)

---

## 14) Reliability & Upgrades

### 14.1 Versioning
- plugin version in header
- schema version stored as option: `ml_schema_version`
- migrations run on `admin_init` if version mismatch

### 14.2 Rewrite rules
- flush on activation/deactivation only (never on every load)

### 14.3 Error handling
- admin notices for validation errors
- do not fatal on missing meta; handle gracefully

---

## 15) CI / Quality Gates (Professional)

### 15.1 Lint & Static Analysis
- PHPCS (WordPress Coding Standards)
- PHPStan (level 5+)
- Optional: Rector rules

### 15.2 Unit Tests
Minimum tests:
- standings calculation (points, GF/GA, ordering)
- player totals aggregation from match rosters/events
- cache invalidation on match update
- playoffs round grouping by stage labels/phases

### 15.3 GitHub Actions (ci.yml)
Steps:
- setup php 8.2
- composer install
- run phpcs
- run phpstan
- run phpunit

---

## 16) Acceptance Criteria (Definition of Done)

1. Admin can create:
   - teams, players, matches
   - fill match editor with rosters + timeline without JSON editing
2. Frontend pages render and are navigable:
   - teams list, fixtures, results, standings, stats, playoffs
3. Standings/stats update after saving a played match (auto recompute) and via manual recompute
4. Team detail page shows team cards + players table + matches tab
5. Player detail shows season accordion + match list with micro-stats
6. Match detail shows metadata, period scores, rosters, timeline
7. Plugin works when switching themes (no dependency on Blocksy templates)
8. Security checks pass (nonces/caps/sanitize/escape)
9. Performance: fixtures/results/standings pages load fast (no heavy loops without cache)

---

## 17) Implementation Notes for Codex/Agent

### 17.1 Build order
1) plugin skeleton + autoload + CPT/TAX registration + activation/migrations
2) admin menu + screens scaffolding
3) match editor UI + save handlers + validation + audit log
4) stats engine + cache + recompute triggers
5) templates + template loader + CSS
6) import/export
7) tests + CI

### 17.2 Strict instruction
- Implement **exactly** the data keys and schemas as written.
- Do not store derived standings in post meta on each team; store in **term_meta** cache keyed by season+competition.
- Ensure all template output uses escaping functions.
- Ensure match editor filters player selection by team.
- Provide clean UX: no clutter, logical sections, sensible defaults.

---

## 18) Legal / Ethical
- Replicate **layout and functional UX**, not copyrighted copy (texts, logos, brand assets).
- MAHL uses its own branding and content.

---

# END OF SPEC
