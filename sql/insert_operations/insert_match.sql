/**
 * Inserts a bet provider data and returns an integer representing the status
 * of the operation. Status codes are:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION insert_match(
    collaborator_id INTEGER, 
    id INTEGER,
    league INTEGER,
    season CHARACTER(9),
    stage INTEGER,
    played_on DATE,
    hometeam INTEGER,
    awayteam INTEGER,
    hometeam_goals INTEGER,
    awayteam_goals INTEGER
) RETURNS QueryResult AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result QueryResult;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert matches';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role NOT IN ('administrator', 'operator') THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert matches';
        RETURN result;
    END IF;

    INSERT INTO match(id, league, season, stage, played_on, hometeam, awayteam, hometeam_goals, awayteam_goals, created_by)
                VALUES(id, league, season, stage, played_on, hometeam, awayteam, hometeam_goals, awayteam_goals, collaborator_id);
    
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