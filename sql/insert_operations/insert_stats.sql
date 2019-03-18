/**
 * Inserts a player stats data and returns a StatsQR containing all the fields
 * in the stats table, which will contain the newly inserted data if successfull,
 * and 3 extra fields: success(boolean), error_code and message. A table of the possible
 * error_codes follows:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION insert_stats(
    collaborator_id INTEGER, 
    player INTEGER,
    attribute_date DATE,
    overall_rating INTEGER,
    potential INTEGER,
    preferred_foot VARCHAR(5),
    attacking_work_rate VARCHAR(6),
    defensive_work_rate VARCHAR(6),
    crossing INTEGER,
    finishing INTEGER,
    heading_accuracy INTEGER,
    short_passing INTEGER,
    volleys INTEGER,
    dribbling INTEGER,
    curve INTEGER,
    free_kick_accuracy INTEGER,
    long_passing INTEGER,
    ball_control INTEGER,
    acceleration INTEGER,
    sprint_speed INTEGER,
    agility INTEGER,
    reactions INTEGER,
    balance INTEGER,
    shot_power INTEGER,
    jumping INTEGER,
    stamina INTEGER,
    strength INTEGER,
    long_shots INTEGER,
    aggression INTEGER,
    interceptions INTEGER,
    positioning INTEGER,
    vision INTEGER,
    penalties INTEGER,
    marking INTEGER,
    standing_tackle INTEGER,
    sliding_tackle INTEGER,
    gk_diving INTEGER,
    gk_handling INTEGER,
    gk_kicking INTEGER,
    gk_positioning INTEGER,
    gk_reflexes INTEGER
) RETURNS StatsQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result StatsQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert players stats';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role <> 'administrator' THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert players';
        RETURN result;
    END IF;

    INSERT INTO stats(
        player,
        attribute_date,
        overall_rating,
        potential,
        preferred_foot,
        attacking_work_rate,
        defensive_work_rate,
        crossing,
        finishing,
        heading_accuracy,
        short_passing,
        volleys,
        dribbling,
        curve,
        free_kick_accuracy,
        long_passing,
        ball_control,
        acceleration,
        sprint_speed,
        agility,
        reactions,
        balance,
        shot_power,
        jumping,
        stamina,
        strength,
        long_shots,
        aggression,
        interceptions,
        positioning,
        vision,
        penalties,
        marking,
        standing_tackle,
        sliding_tackle,
        gk_diving,
        gk_handling,
        gk_kicking,
        gk_positioning,
        gk_reflexes
    ) VALUES (
        player,
        attribute_date,
        overall_rating,
        potential,
        preferred_foot,
        attacking_work_rate,
        defensive_work_rate,
        crossing,
        finishing,
        heading_accuracy,
        short_passing,
        volleys,
        dribbling,
        curve,
        free_kick_accuracy,
        long_passing,
        ball_control,
        acceleration,
        sprint_speed,
        agility,
        reactions,
        balance,
        shot_power,
        jumping,
        stamina,
        strength,
        long_shots,
        aggression,
        interceptions,
        positioning,
        vision,
        penalties,
        marking,
        standing_tackle,
        sliding_tackle,
        gk_diving,
        gk_handling,
        gk_kicking,
        gk_positioning,
        gk_reflexes
    )
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
        stats.gk_reflexes
    INTO result;
    
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