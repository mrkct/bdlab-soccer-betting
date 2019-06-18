/**
 * Inserts a match data and returns a MatchQR containing all the fields
 * in the match table, which will contain the newly inserted data if successfull,
 * and 3 extra fields: success(boolean), error_code and message. A table of the possible
 * error_codes follows:
 * 0    : OK
 * -1   : User is not allowed to insert
 * -2   : Duplicate row
 * -3   : Foreign key violation
 * When an error occurs outside the ones specified above an exception is thrown
 */
CREATE OR REPLACE FUNCTION insert_match(
    collaborator_id INTEGER, 
    id INTEGER,
    league INTEGER,
    season CHARACTER(9),
    stage INTEGER,
    played_on DATE,
    hometeam INTEGER,
    awayteam INTEGER,
    hometeam_goals INTEGER,
    awayteam_goals INTEGER
) RETURNS MatchQR AS $$
DECLARE
    current_collaborator soccer.collaborator%ROWTYPE;
    result MatchQR;
BEGIN
    IF collaborator_id IS NULL THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert matches';
        RETURN result;
    END IF;

    SELECT * INTO current_collaborator 
    FROM collaborator 
    WHERE collaborator.id = collaborator_id;

    IF NOT FOUND OR current_collaborator.role NOT IN ('administrator', 'operator') THEN
        result.success := FALSE;
        result.error_code := -1;
        result.message := 'User is not allowed to insert matches';
        RETURN result;
    END IF;

    IF id IS NOT NULL THEN
        INSERT INTO match(
            id, league, season, stage, played_on, hometeam, awayteam, 
            hometeam_goals, awayteam_goals, created_by
        ) VALUES (
            id, league, season, stage, played_on, hometeam, awayteam, 
            hometeam_goals, awayteam_goals, collaborator_id
        )
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
        -- Here we fix the serial id, since it probably got messed up by this insert
        PERFORM setval('soccer.match_id_seq', COALESCE((SELECT MAX(match.id)+1 FROM match), 1), false);
    ELSE
        INSERT INTO match(
            league, season, stage, played_on, hometeam, awayteam, 
            hometeam_goals, awayteam_goals, created_by
        ) VALUES (
            league, season, stage, played_on, hometeam, awayteam, 
            hometeam_goals, awayteam_goals, collaborator_id
        )
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