BEGIN TRANSACTION;

CREATE TABLE IF NOT EXISTS player(
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    birthday DATE,
    height DOUBLE PRECISION,
    weight DOUBLE PRECISION
);

CREATE TABLE IF NOT EXISTS stats(
    player INTEGER REFERENCES player(id),
    attribute_date DATE NOT NULL,
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
    CHECK (preferred_foot IN ('right', 'left')),
    CHECK (attacking_work_rate IN ('low', 'medium', 'high')),
    CHECK (defensive_work_rate IN ('low', 'medium', 'high')),
    CHECK(crossing >= 0 AND crossing <= 100),
    CHECK(finishing >= 0 AND finishing <= 100),
    CHECK(heading_accuracy >= 0 AND heading_accuracy <= 100),
    CHECK(short_passing >= 0 AND short_passing <= 100),
    CHECK(volleys >= 0 AND volleys <= 100),
    CHECK(dribbling >= 0 AND dribbling <= 100),
    CHECK(curve >= 0 AND curve <= 100),
    CHECK(free_kick_accuracy >= 0 AND free_kick_accuracy <= 100),
    CHECK(long_passing >= 0 AND long_passing <= 100),
    CHECK(ball_control >= 0 AND ball_control <= 100),
    CHECK(acceleration >= 0 AND acceleration <= 100),
    CHECK(sprint_speed >= 0 AND sprint_speed <= 100),
    CHECK(agility >= 0 AND agility <= 100),
    CHECK(reactions >= 0 AND reactions <= 100),
    CHECK(balance >= 0 AND balance <= 100),
    CHECK(shot_power >= 0 AND shot_power <= 100),
    CHECK(jumping >= 0 AND jumping <= 100),
    CHECK(stamina >= 0 AND stamina <= 100),
    CHECK(strength >= 0 AND strength <= 100),
    CHECK(long_shots >= 0 AND long_shots <= 100),
    CHECK(aggression >= 0 AND aggression <= 100),
    CHECK(interceptions >= 0 AND interceptions <= 100),
    CHECK(positioning >= 0 AND positioning <= 100),
    CHECK(vision >= 0 AND vision <= 100),
    CHECK(penalties >= 0 AND penalties <= 100),
    CHECK(marking >= 0 AND marking <= 100),
    CHECK(standing_tackle >= 0 AND standing_tackle <= 100),
    CHECK(sliding_tackle >= 0 AND sliding_tackle <= 100),
    CHECK(gk_diving >= 0 AND gk_diving <= 100),
    CHECK(gk_handling >= 0 AND gk_handling <= 100),
    CHECK(gk_kicking >= 0 AND gk_kicking <= 100),
    CHECK(gk_positioning >= 0 AND gk_positioning <= 100),
    CHECK(gk_reflexes >= 0 AND gk_reflexes <= 100),
    PRIMARY KEY(player, attribute_date)
);

CREATE TABLE IF NOT EXISTS team(
    id SERIAL PRIMARY KEY,
    shortname VARCHAR(100) NOT NULL,
    longname VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS league(
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    country VARCHAR(255) NOT NULL DEFAULT 'Unknown'
);

CREATE TABLE IF NOT EXISTS bet_provider(
    id VARCHAR(5) PRIMARY KEY,
    name VARCHAR(255)
);

-- Aka the users table
CREATE TABLE IF NOT EXISTS collaborator(
    id SERIAL PRIMARY KEY,
    role VARCHAR(50) NOT NULL ,
    name VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    affiliation VARCHAR(5) REFERENCES bet_provider(id),
    CHECK (role IN ('administrator', 'operator', 'partner')),
    CHECK (
        (affiliation IS NOT NULL AND role = 'partner') OR 
        (affiliation IS NULL AND role <> 'partner')
    )
);

CREATE TABLE IF NOT EXISTS match(
    id SERIAL PRIMARY KEY,
    league INTEGER REFERENCES league(id),
    season CHAR(9),
    stage INTEGER NOT NULL,
    played_on DATE NOT NULL,
    hometeam_goals INTEGER NOT NULL CHECK (hometeam_goals >= 0),
    awayteam_goals INTEGER NOT NULL CHECK (hometeam_goals >= 0),
    hometeam INTEGER REFERENCES team(id),
    awayteam INTEGER REFERENCES team(id),
    created_by INTEGER NOT NULL REFERENCES collaborator(id),
    CHECK (hometeam <> awayteam),
    CHECK (stage > 0)
);

CREATE TABLE IF NOT EXISTS played(
    player INTEGER REFERENCES player(id),
    match INTEGER REFERENCES match(id),
    team INTEGER NOT NULL REFERENCES team(id),
    PRIMARY KEY(player, match)
);

CREATE TABLE IF NOT EXISTS quote(
    match INTEGER REFERENCES match(id),
    bet_provider VARCHAR(5) REFERENCES bet_provider(id),
    home_quote DOUBLE PRECISION NOT NULL,
    draw_quote DOUBLE PRECISION NOT NULL,
    away_quote DOUBLE PRECISION NOT NULL,
    created_by INTEGER NOT NULL REFERENCES collaborator(id),
    PRIMARY KEY(match, bet_provider),
);

COMMIT;