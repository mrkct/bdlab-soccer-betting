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

CREATE OR REPLACE FUNCTION refresh_rankings() RETURNS TRIGGER AS $$ 
BEGIN
    REFRESH MATERIALIZED VIEW rankings;
    RETURN NEW;
END;
$$ language plpgsql;

CREATE TRIGGER trg_refresh_rankings AFTER INSERT OR UPDATE OR DELETE ON match FOR EACH STATEMENT EXECUTE PROCEDURE refresh_rankings();