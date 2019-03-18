/**
 *  Data type returned by all functions insert_*.
 *  When an operations completes successfully the fields are all
 *  completed except the 'message'. If success is false then the data
 *  in the other fields might not be correct. Usually the other fields
 *  (not success, error_code and message) contain the just inserted/edited
 *  row
 */


CREATE TYPE BetProviderQR AS (
    id VARCHAR(5),
    name VARCHAR(255),
    -- Query result fields
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(255)
);

CREATE TYPE LeagueQR AS (
    id INTEGER,
    name VARCHAR(255),
    country VARCHAR(255),
    -- Query result fields
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(255)
);

CREATE TYPE MatchQR AS (
    id INTEGER,
    league INTEGER,
    season CHAR(9),
    stage INTEGER,
    played_on DATE,
    hometeam_goals INTEGER,
    awayteam_goals INTEGER,
    hometeam INTEGER,
    awayteam INTEGER,
    created_by INTEGER,
    -- Query result fields
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(255)
);

CREATE TYPE PlayerQR AS (
    id INTEGER,
    name VARCHAR(255),
    birthday DATE,
    height DOUBLE PRECISION,
    weight DOUBLE PRECISION,
    -- Query result fields
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(255)
);

CREATE TYPE QuoteQR AS (
    match INTEGER,
    bet_provider VARCHAR(5),
    home_quote DOUBLE PRECISION,
    draw_quote DOUBLE PRECISION,
    away_quote DOUBLE PRECISION,
    created_by INTEGER,
    -- Query result fields
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(255)
);

CREATE TYPE StatsQR AS (
    player INTEGER,
    attribute_date DATE,
    overall_rating INTEGER,
    potential INTEGER,
    preferred_foot VARCHAR(5),
    attacking_work_rate VARCHAR(6),
    defensive_work_rate VARCHAR(6),
    crossing INTEGER,
    finishing INTEGER,
    heading_accuracy INTEGER,
    short_passing INTEGER,
    volleys INTEGER,
    dribbling INTEGER,
    curve INTEGER,
    free_kick_accuracy INTEGER,
    long_passing INTEGER,
    ball_control INTEGER,
    acceleration INTEGER,
    sprint_speed INTEGER,
    agility INTEGER,
    reactions INTEGER,
    balance INTEGER,
    shot_power INTEGER,
    jumping INTEGER,
    stamina INTEGER,
    strength INTEGER,
    long_shots INTEGER,
    aggression INTEGER,
    interceptions INTEGER,
    positioning INTEGER,
    vision INTEGER,
    penalties INTEGER,
    marking INTEGER,
    standing_tackle INTEGER,
    sliding_tackle INTEGER,
    gk_diving INTEGER,
    gk_handling INTEGER,
    gk_kicking INTEGER,
    gk_positioning INTEGER,
    gk_reflexes INTEGER,
    -- Query result fields
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(255)
);

CREATE TYPE TeamQR AS (
    id INTEGER,
    shortname VARCHAR(100),
    longname VARCHAR(255),
    -- Query result fields
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(255)
);

CREATE TYPE PlayedQR AS (
    player INTEGER,
    match INTEGER,
    team INTEGER,
    -- Query result fields
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(255)
);

CREATE TYPE CollaboratorQR AS (
    id INTEGER,
    role VARCHAR(50),
    name VARCHAR(255),
    password VARCHAR(255),
    affiliation VARCHAR(5),
    -- Query result fields
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(255)
);