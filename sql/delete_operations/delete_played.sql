/**
 * Deletes a player partecipation in a match given its primary key and returns the deleted row.
 * A table of the possible error_codes follows:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION delete_played(
    collaborator_id INTEGER, 
    player_id INTEGER,
    match_id INTEGER
) RETURNS PlayedQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result PlayedQR;
    match soccer.match%ROWTYPE;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to delete players partecipations';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role NOT IN ('administrator', 'operator') THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to delete players partecipations';
        RETURN result;
    END IF;

    SELECT * INTO match FROM match WHERE id = match_id;

    IF current_collaborator.role = 'operator' AND match.created_by <> collaborator_id THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'Only the person who added the match can delete the players who played in it';
        RETURN result;
    END IF;

    DELETE
        FROM played
        WHERE match = match_id AND player = player_id
        RETURNING
            played.player,
            played.match,
            played.team INTO result;

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