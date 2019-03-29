/**
 * Deletes a quote for a match given its primary key and returns the deleted row.
 * A table of the possible error_codes follows:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION delete_quote(
    collaborator_id INTEGER, 
    match_id INTEGER,
    bet_provider_id VARCHAR(5)
) RETURNS QuoteQR AS $$
DECLARE
    c_user soccer.collaborator%ROWTYPE;
    result QuoteQR;
    quote_todelete quote%ROWTYPE;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to delete betting providers';
        RETURN result;
    END IF;

    SELECT * INTO c_user 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR c_user.role NOT IN ('administrator', 'partner') THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to delete quotes';
        RETURN result;
    END IF;

    IF c_user.role = 'partner' AND c_user.affiliation <> bet_provider THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to delete quotes for this betting provider';
        RETURN result;
    END IF;

    SELECT * INTO quote_todelete FROM quote WHERE match = match_id AND bet_provider = bet_provider_id;
    IF c_user.id <> quote_todelete.created_by THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'Only the user who added this quote can delete it';
        RETURN result;
    END IF;

    INSERT INTO quote(match, bet_provider, home_quote, draw_quote, away_quote, created_by)
                VALUES(match, bet_provider, home_quote, draw_quote, away_quote, collaborator_id)
    RETURNING
        quote.match, 
        quote.bet_provider, 
        quote.home_quote, 
        quote.draw_quote, 
        quote.away_quote, 
        quote.created_by
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