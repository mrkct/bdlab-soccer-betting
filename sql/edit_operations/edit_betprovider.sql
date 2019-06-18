/**
 * Edits a bet provider data and returns a BetProviderQR containing all the fields
 * in the bet_provider table, which will contain the newly edited data if successfull,
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
CREATE OR REPLACE FUNCTION edit_bet_provider(
    collaborator_id INTEGER, 
    current_id VARCHAR(5),
    new_id VARCHAR(5),
    new_name VARCHAR(255)
) RETURNS BetProviderQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result BetProviderQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit betting providers';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role <> 'administrator' THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit betting providers';
        RETURN result;
    END IF;

    UPDATE bet_provider 
    SET 
        id = new_id, 
        name = new_name 
    WHERE bet_provider.id = current_id
    RETURNING
        id, name INTO result;
    
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

--
-- Test inserts
--
/*
    select * from insert_bet_provider(2, 'XXXXX', 'test');
    select * from insert_bet_provider(7, 'XXXXX', 'test');
*/