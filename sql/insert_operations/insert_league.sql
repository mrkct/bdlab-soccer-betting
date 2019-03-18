/**
 * Inserts a league data and returns a LeagueQR containing all the fields
 * in the league table, which will contain the newly inserted data if successfull,
 * and 3 extra fields: success(boolean), error_code and message. A table of the possible
 * error_codes follows:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION insert_league(
    collaborator_id INTEGER, 
    league_id INTEGER, 
    name VARCHAR(255), 
    country VARCHAR(255)
) RETURNS LeagueQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result LeagueQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert leagues';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role <> 'administrator' THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert leagues';
        RETURN result;
    END IF;

    IF league_id IS NOT NULL THEN
        -- Even if it would be good we can't use a transaction here. Postgres doesn't support them in functions
        INSERT 
            INTO league(id, name, country) 
            VALUES (league_id, name, country) 
            RETURNING 
                league.id, 
                league.name, 
                league.country 
            INTO result;
        -- Here we fix the serial id, since it probably got messed up by this insert
        PERFORM setval('soccer.league_id_seq', COALESCE((SELECT MAX(id)+1 FROM league), 1), false);
    ELSE
        INSERT 
            INTO league(name, country) 
            VALUES (name, country) 
            RETURNING 
                league.id, 
                league.name, 
                league.country
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

-- Test inserts
/**
    select * from insert_league(2, NULL, 'new insert!', 'Italy');
*/