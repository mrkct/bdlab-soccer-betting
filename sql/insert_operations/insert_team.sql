/**
 * Inserts a team's data and returns a TeamQR containing all the fields
 * in the team table, which will contain the newly inserted data if successfull,
 * and 3 extra fields: success(boolean), error_code and message. A table of the possible
 * error_codes follows:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION insert_team(
    collaborator_id INTEGER, 
    team_id INTEGER, 
    shortname VARCHAR(100),
    longname VARCHAR(255)
) RETURNS TeamQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result TeamQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert teams';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role <> 'administrator' THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert teams';
        RETURN result;
    END IF;

    IF team_id IS NOT NULL THEN
        INSERT INTO team(id, shortname, longname) 
                    VALUES(team_id, shortname, longname)
        RETURNING
            team.id, 
            team.shortname, 
            team.longname
        INTO result;
        -- Here we fix the serial id, since it probably got messed up by this insert
        PERFORM setval('soccer.team_id_seq', COALESCE((SELECT MAX(team.id)+1 FROM team), 1), false);
    ELSE
        INSERT INTO team(shortname, longname) 
                    VALUES(shortname, longname)
        RETURNING
            team.id, 
            team.shortname, 
            team.longname
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