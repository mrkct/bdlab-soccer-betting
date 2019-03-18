/**
 * Inserts a player data and returns a PlayerQR containing all the fields
 * in the player table, which will contain the newly inserted data if successfull,
 * and 3 extra fields: success(boolean), error_code and message. A table of the possible
 * error_codes follows:
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
) RETURNS PlayerQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result PlayerQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert players';
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

    IF player_id IS NOT NULL THEN
        INSERT INTO player(id, name, birthday, height, weight) 
                    VALUES(player_id, name, birthday, height, weight)
        RETURNING
            player.id, 
            player.name, 
            player.birthday, 
            player.height, 
            player.weight
        INTO result;
        -- Here we fix the serial id, since it probably got messed up by this insert
        PERFORM setval('soccer.player_id_seq', COALESCE((SELECT MAX(player.id)+1 FROM player), 1), false);
    ELSE
        INSERT INTO player(name, birthday, height, weight) 
                    VALUES(name, birthday, height, weight)
        RETURNING
            player.id, 
            player.name, 
            player.birthday, 
            player.height, 
            player.weight
        INTO result;
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