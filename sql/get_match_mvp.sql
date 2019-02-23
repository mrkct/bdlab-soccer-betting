CREATE OR REPLACE FUNCTION get_match_mvp(match_id INTEGER, team_id INTEGER) RETURNS SETOF soccer.player AS $$
DECLARE
    match_date DATE;
BEGIN
    SELECT played_on INTO match_date FROM match WHERE id = match_id;
    RETURN QUERY 
        WITH players AS (
            SELECT stats.player, stats.overall_rating
            FROM played
            JOIN most_recent_stats(match_date) AS stats ON stats.player = played.player
            WHERE played.match = match_id AND played.team = team_id
        )
        SELECT player.*
        FROM players
        JOIN player ON players.player = player.id
        WHERE players.overall_rating >= (SELECT MAX(overall_rating) FROM players);
END;
$$ language 'plpgsql';

select * from get_match_mvp(145, 9996);