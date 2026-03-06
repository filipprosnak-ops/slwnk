# Data Model

Custom Post Types:
- mahl_season
- mahl_phase
- mahl_team
- mahl_player
- mahl_game

Relationships:
- Phase -> Season
- Team -> Season
- Player -> Team
- Game -> Season
- Game -> Phase

Competition hierarchy:
- Season = competition year, e.g. MAHL 2026/2027
- Phase = competition stage within a season, e.g. Pre Season, Základná časť, Nadstavba, Playoff
- Round = numeric game field
- Group/Bracket = optional game field for splits such as Top, Bottom, Semifinal, Final

Game Fields:
- season
- phase
- round_number
- group_key
- match_date
- match_time
- venue
- home_team
- away_team
- status
- score_home_final
- score_away_final
- score_home_period_1
- score_away_period_1
- score_home_period_2
- score_away_period_2
- score_home_period_3
- score_away_period_3
- overtime_played
- shootout_played
- score_home_overtime
- score_away_overtime
- score_home_shootout
- score_away_shootout
- notes

Player statistics:
- games_played
- goals
- assists
- points
- penalty_minutes

Team standings:
- games_played
- wins
- losses
- goals_for
- goals_against
- goal_difference
- points
- calculable by season
- calculable by season + phase
- calculable by season + phase + optional group/bracket