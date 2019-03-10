/**
 * Inserts a quote for a match and returns an integer representing the status
 * of the operation. Status codes are:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION insert_quote(
    collaborator_id INTEGER, 
    match INTEGER,
    bet_provider VARCHAR(5),
    home_quote DOUBLE PRECISION,
    draw_quote DOUBLE PRECISION,
    away_quote DOUBLE PRECISION
) RETURNS QueryResult AS $$
DECLARE
    c_user soccer.collaborator%ROWTYPE;
    result QueryResult;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert betting providers';
        RETURN result;
    END IF;

    SELECT * INTO c_user 
    FROM collaborator 
    WHERE id = collaborator_id;

    IF NOT FOUND OR c_user.role NOT IN ('administrator', 'partner') THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert quotes';
        RETURN result;
    END IF;

    IF c_user.role = 'partner' AND c_user.affiliation <> bet_provider THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert quotes for this betting provider';
        RETURN result;
    END IF;

    INSERT INTO quote(match, bet_provider, home_quote, draw_quote, away_quote, created_by)
                VALUES(match, bet_provider, home_quote, draw_quote, away_quote, collaborator_id);
    
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