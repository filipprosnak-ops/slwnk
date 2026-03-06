# Data Model

Custom Post Types:
- mahl_season
- mahl_team
- mahl_player
- mahl_game

Relationships:
- Team -> Season
- Player -> Team
- Game -> Season

Game Fields:
- date
- time
- venue
- home_team
- away_team
- score_home
- score_away
- status

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