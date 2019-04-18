/**
 * Edits a quote data and returns a QuoteQR containing all the fields
 * in the quote table, which will contain the newly edited data if successfull,
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
CREATE OR REPLACE FUNCTION edit_quote(
    collaborator_id INTEGER, 
    current_match INTEGER,
    current_betprovider VARCHAR(5),
    new_home_quote DOUBLE PRECISION,
    new_draw_quote DOUBLE PRECISION,
    new_away_quote DOUBLE PRECISION
) RETURNS QuoteQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result QuoteQR;
    old_row soccer.quote%ROWTYPE;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit quotes';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role NOT IN('administrator', 'partner') THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit quotes';
        RETURN result;
    END IF;

    SELECT * INTO old_row FROM quote WHERE quote.match = current_match AND quote.bet_provider = current_betprovider;

    IF old_row.created_by <> current_collaborator.id THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to edit this quote';
        RETURN result;
    END IF;
    
    UPDATE quote 
    SET 
        home_quote = new_home_quote,
        draw_quote = new_draw_quote,
        away_quote = new_away_quote
    WHERE quote.match = current_match AND quote.bet_provider = current_betprovider
    RETURNING
        match, 
        bet_provider, 
        home_quote, 
        draw_quote, 
        away_quote, 
        created_by INTO result;
    
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