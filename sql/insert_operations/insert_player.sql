/**
 * Inserts a player data and returns an integer representing the status
 * of the operation. Status codes are:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION insert_player(
    collaborator_id INTEGER, 
    player_id INTEGER, 
    name VARCHAR(255), 
    birthday DATE, 
    height DOUBLE PRECISION, 
    weight DOUBLE PRECISION 
) RETURNS QueryResult AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result QueryResult;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert players';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role <> 'administrator' THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert players';
        RETURN result;
    END IF;

    IF player_id IS NOT NULL THEN
        INSERT INTO player(id, name, birthday, height, weight) 
                    VALUES(player_id, name, birthday, height, weight);
    ELSE
        INSERT INTO player(name, birthday, height, weight) 
                    VALUES(name, birthday, height, weight);
    END IF;
    
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