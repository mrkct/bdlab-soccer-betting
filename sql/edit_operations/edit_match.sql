/**
 * Edits a match data and returns a MatchQR containing all the fields
 * in the match table, which will contain the newly edited data if successfull,
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
CREATE OR REPLACE FUNCTION edit_match(
    collaborator_id INTEGER, 
    current_id INTEGER,
    new_league INTEGER,
    new_season CHAR(9),
    new_stage INTEGER,
    new_played_on DATE,
    new_hometeam_goals INTEGER,
    new_awayteam_goals INTEGER,
    new_hometeam INTEGER,
    new_awayteam INTEGER
) RETURNS MatchQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result MatchQR;
    old_row Match%ROWTYPE;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit matches';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role NOT IN ('administrator', 'operator') THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit matches';
        RETURN result;
    END IF;

    SELECT * INTO old_row FROM match WHERE match.id = current_id;

    IF current_collaborator.role = 'operator' AND old_row.created_by <> current_collaborator.id THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit matches';
        RETURN result;
    END IF;
    
    UPDATE match 
    SET 
        league = new_league,
        season = new_season,
        stage = new_stage,
        played_on = new_played_on,
        hometeam_goals = new_hometeam_goals,
        awayteam_goals = new_awayteam_goals,
        hometeam = new_hometeam,
        awayteam = new_awayteam
    WHERE match.id = current_id
    RETURNING
        id, 
        league, 
        season, 
        stage, 
        played_on, 
        hometeam_goals, 
        awayteam_goals,
        hometeam,
        awayteam,
        created_by 
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