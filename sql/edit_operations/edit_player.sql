/**
 * Edits a player data and returns a PlayerQR containing all the fields
 * in the player table, which will contain the newly edited data if successfull,
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
CREATE OR REPLACE FUNCTION edit_player(
    collaborator_id INTEGER, 
    current_id INTEGER,
    new_id INTEGER,
    new_name VARCHAR(255),
    new_birthday DATE,
    new_height DOUBLE PRECISION,
    new_weight DOUBLE PRECISION
) RETURNS PlayerQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result PlayerQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit players';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role <> 'administrator' THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit players';
        RETURN result;
    END IF;

    UPDATE player 
    SET 
        id = new_id, 
        name = new_name,
        birthday = new_birthday,
        height = new_height,
        weight = new_weight
    WHERE player.id = current_id
    RETURNING
        id, name, birthday, height, weight INTO result;
    
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