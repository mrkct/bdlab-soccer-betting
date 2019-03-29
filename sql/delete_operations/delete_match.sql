/**
 * Deletes a match given its primary key and returns the deleted row.
 * This will delete all the related quotes and played entries. A table of the possible
 * error_codes follows:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION delete_match(
    collaborator_id INTEGER, 
    match_id INTEGER
) RETURNS MatchQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result MatchQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to delete matches';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role NOT IN ('administrator', 'operator') THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to delete matches';
        RETURN result;
    END IF;

    DELETE
        FROM match
        WHERE id = match_id
        RETURNING
            match.id, 
            match.league, 
            match.season, 
            match.stage, 
            match.played_on, 
            match.hometeam, 
            match.awayteam, 
            match.hometeam_goals, 
            match.awayteam_goals, 
            match.created_by
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