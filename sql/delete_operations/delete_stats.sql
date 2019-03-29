/**
 * Deletes a player's stats rilevation given its primary key and returns the deleted row.
 * A table of the possible error_codes follows:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION delete_stats(
    collaborator_id INTEGER, 
    player_id INTEGER,
    attribute_date_id DATE
) RETURNS StatsQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result StatsQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to delete players stats';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role <> 'administrator' THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to delete players stats';
        RETURN result;
    END IF;

    DELETE 
        FROM stats 
        WHERE player = player_id AND attribute_date = attribute_date_id
        RETURNING
            stats.player,
            stats.attribute_date,
            stats.overall_rating,
            stats.potential,
            stats.preferred_foot,
            stats.attacking_work_rate,
            stats.defensive_work_rate,
            stats.crossing,
            stats.finishing,
            stats.heading_accuracy,
            stats.short_passing,
            stats.volleys,
            stats.dribbling,
            stats.curve,
            stats.free_kick_accuracy,
            stats.long_passing,
            stats.ball_control,
            stats.acceleration,
            stats.sprint_speed,
            stats.agility,
            stats.reactions,
            stats.balance,
            stats.shot_power,
            stats.jumping,
            stats.stamina,
            stats.strength,
            stats.long_shots,
            stats.aggression,
            stats.interceptions,
            stats.positioning,
            stats.vision,
            stats.penalties,
            stats.marking,
            stats.standing_tackle,
            stats.sliding_tackle,
            stats.gk_diving,
            stats.gk_handling,
            stats.gk_kicking,
            stats.gk_positioning,
            stats.gk_reflexes INTO result;
    
    result.success := TRUE;
    result.error_code := 0;
    result.message := NULL;
    
    RETURN result;

    EXCEPTION
        WHEN unique_violation THEN
            result.success := FALSE;
            result.error_code := -2;
            result.message := 'A row with the same primary key already exists';
            RETURN result;
        WHEN foreign_key_violation THEN
            result.success := FALSE;
            result.error_code := -3;
            result.message := 'A foreign key violation occurred';
            RETURN result;
END;
$$ language 'plpgsql';
