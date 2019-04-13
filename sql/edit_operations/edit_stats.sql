/**
 * Edits a player's stats data and returns a StatsQR containing all the fields
 * in the stats table, which will contain the newly edited data if successfull,
 * and 3 extra fields: success(boolean), error_code and message.
 * NOTE: If the passed current id is not valid(eg: there is no row with that id) it will
 * still return success but all the other fields will be empty. 
 * A table of the possible error_codes follows:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION edit_stats(
    collaborator_id INTEGER, 
    current_player INTEGER,
    current_attribute_date DATE,
    new_attribute_date DATE,
    new_overall_rating INTEGER,
    new_potential INTEGER,
    new_preferred_foot VARCHAR(5),
    new_attacking_work_rate VARCHAR(6),
    new_defensive_work_rate VARCHAR(6),
    new_crossing INTEGER,
    new_finishing INTEGER,
    new_heading_accuracy INTEGER,
    new_short_passing INTEGER,
    new_volleys INTEGER,
    new_dribbling INTEGER,
    new_curve INTEGER,
    new_free_kick_accuracy INTEGER,
    new_long_passing INTEGER,
    new_ball_control INTEGER,
    new_acceleration INTEGER,
    new_sprint_speed INTEGER,
    new_agility INTEGER,
    new_reactions INTEGER,
    new_balance INTEGER,
    new_shot_power INTEGER,
    new_jumping INTEGER,
    new_stamina INTEGER,
    new_strength INTEGER,
    new_long_shots INTEGER,
    new_aggression INTEGER,
    new_interceptions INTEGER,
    new_positioning INTEGER,
    new_vision INTEGER,
    new_penalties INTEGER,
    new_marking INTEGER,
    new_standing_tackle INTEGER,
    new_sliding_tackle INTEGER,
    new_gk_diving INTEGER,
    new_gk_handling INTEGER,
    new_gk_kicking INTEGER,
    new_gk_positioning INTEGER,
    new_gk_reflexes INTEGER
) RETURNS StatsQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result StatsQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit players stats';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role <> 'administrator' THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit player stats';
        RETURN result;
    END IF;
    
    UPDATE stats 
    SET 
        attribute_date = new_attribute_date,
        overall_rating = new_overall_rating,
        potential = new_potential,
        preferred_foot = new_preferred_foot,
        attacking_work_rate = new_attacking_work_rate,
        defensive_work_rate = new_defensive_work_rate,
        crossing = new_crossing,
        finishing = new_finishing,
        heading_accuracy = new_heading_accuracy,
        short_passing = new_short_passing,
        volleys = new_volleys,
        dribbling = new_dribbling,
        curve = new_curve,
        free_kick_accuracy = new_free_kick_accuracy,
        long_passing = new_long_passing,
        ball_control = new_ball_control,
        acceleration = new_acceleration,
        sprint_speed = new_sprint_speed,
        agility = new_agility,
        reactions = new_reactions,
        balance = new_balance,
        shot_power = new_shot_power,
        jumping = new_jumping,
        stamina = new_stamina,
        strength = new_strength,
        long_shots = new_long_shots,
        aggression = new_aggression,
        interceptions = new_interceptions,
        positioning = new_positioning,
        vision = new_vision,
        penalties = new_penalties,
        marking = new_marking,
        standing_tackle = new_standing_tackle,
        sliding_tackle = new_sliding_tackle,
        gk_diving = new_gk_diving,
        gk_handling = new_gk_handling,
        gk_kicking = new_gk_kicking,
        gk_positioning = new_gk_positioning,
        gk_reflexes = new_gk_reflexes
    WHERE stats.player = current_player AND stats.attribute_date = current_attribute_date
    RETURNING
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
        gk_reflexes INTO result;
    
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