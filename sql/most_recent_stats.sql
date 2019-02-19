/**
 * Returns a stats table containing only the most recent available stat for each player
 * based on the passed date
 */
CREATE OR REPLACE FUNCTION most_recent_stats(d date) RETURNS SETOF soccer.stats AS $$
BEGIN
    RETURN QUERY
        SELECT stats.*
        FROM stats
        JOIN (
            SELECT player, MAX(attribute_date) as attribute_date
            FROM soccer.stats
            WHERE attribute_date <= d
            GROUP BY player
        ) AS RS ON RS.player = stats.player AND RS.attribute_date = stats.attribute_date;
END;
$$ language 'plpgsql';