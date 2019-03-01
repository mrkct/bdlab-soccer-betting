CREATE MATERIALIZED VIEW rankings AS(
    WITH partecipations(league, season, team) AS (
        SELECT DISTINCT league, season, hometeam as team
        FROM match
        UNION
        SELECT DISTINCT league, season, awayteam as team
        FROM match
    ) 
    SELECT P.league, P.season, P.team, COUNT(*) AS won_games
    FROM partecipations AS P
    LEFT JOIN match ON 
        match.league = P.league AND 
        match.season = P.season AND 
        (
            (match.hometeam = P.team AND match.hometeam_goals > match.awayteam_goals) 
            OR 
            (match.awayteam = P.team AND match.awayteam_goals > match.hometeam_goals)
        )
    GROUP BY P.league, P.season, P.team
);